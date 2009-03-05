<?php
/**
 * 
 * @package ActiveRecord
 * 
 * IMPROVE: Extract Associations into ActiveRecord::Associations.
 * TODO: Implement eager loading of associations (:include => 'assoc').
 * TODO: Implement :throught associations.
 * TODO: Implement has_and_belongs_to_many association.
 * 
 * TODO: Implement calculations.
 * IMPROVE: Implement find_:scope_by_:column() magic methods. 
 */
class ActiveRecord_Base extends ActiveRecord_Validations
{
  protected $db;
  protected $table_name;
  protected $primary_key = 'id';
  protected $columns     = array();
  
  protected $associations = array(
    'belongs_to' => array(),
    'has_one'    => array(),
    'has_many'   => array(),
  );
  protected $belongs_to   = array();
  protected $has_one      = array();
  protected $has_many     = array();
  
  
  # IMPROVE: Check if columns do not conflict with object class attributes.
  function __construct($arg=null)
  {
    # database connection
    $this->table_name = String::underscore(String::pluralize(get_class($this)));
    $this->db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    
    # columns' definition
    $apc_key = TMP.'/cache/active_records/columns_'.$this->table_name;
    $this->columns = apc_fetch($apc_key, $success);
    if ($success === false)
    {
      $this->columns = $this->db->columns($this->table_name);
      apc_store($apc_key, $this->columns);
    }
    
    # relationships
    $this->configure_associations('belongs_to');
    $this->configure_associations('has_one');
    $this->configure_associations('has_many');
    
    # args
    if ($arg !== null)
    {
      if (!is_array($arg))
      {
        $arg = $this->find($arg);
        $this->new_record = false;
      }
      $this->set_attributes($arg);
    }
  }
  
  /**
   * Sets the record's attributes.
   * 
   * IMPROVE: Move to ActiveRecord::Record?
   */
  protected function set_attributes($arg)
  {
    foreach($arg as $attribute => $value) {
      $this->$attribute = $value;
    }
  }
  
  private function configure_associations($type)
  {
    foreach($this->$type as $i => $assoc)
    {
      if (is_integer($i))
      {
        $name = $assoc;
        unset($this->$type[$i]);
        $this->$type[$name] = array();
      }
      else {
        $name = $i;
      }
      $def =& $this->{$type}[$name];
      
      if (empty($def['table'])) {
        $def['table'] = String::pluralize(String::underscore($name));
      }
      if (empty($def['primary_key'])) {
        $def['primary_key'] = 'id';
      }
      if (empty($def['foreign_key']))
      {
        switch($type)
        {
          case 'belongs_to':
            $def['foreign_key'] = String::underscore($name).'_'.$def['primary_key'];
          break;
          
          case 'has_one':
            $def['foreign_key'] = String::underscore(get_class($this)).'_'.$this->primary_key;
          break;
          
          case 'has_many':
            $def['foreign_key'] = String::underscore(get_class($this)).'_'.$this->primary_key;
          break;
        }
      }
    }
  }
  
  function __set($attribute, $value)
  {
    if (isset($this->columns[$attribute]))
    {
      if ($value !== null)
      {
        switch($this->columns[$attribute]['type'])
        {
          case 'integer': $value = (int)$value;    break;
          case 'double':  $value = (double)$value; break;
          case 'bool':    $value = (bool)$value;   break;
        }
      }
      return parent::__set($attribute, $value);
    }
    else {
      return $this->$attribute = $value;
    }
  }
  
  function __get($attribute)
  {
    # field
#    if (in_array($attribute, array_keys($this->columns))) {
#      return parent::__get($attribute);
#    }
    
    # association: belongs to
#    elseif (array_key_exists($attribute, $this->belongs_to))
    if (array_key_exists($attribute, $this->belongs_to))
    {
      $conditions = array($this->belongs_to[$attribute]['primary_key'] => $this->{$this->belongs_to[$attribute]['foreign_key']});
      $class      = String::camelize($attribute);
      $record     = new $class();
      return $this->$attribute = $record->find(':first', array('conditions' => &$conditions));
    }
    
    # association: has one
    elseif (array_key_exists($attribute, $this->has_one))
    {
      $conditions = array($this->has_one[$attribute]['foreign_key'] => $this->{$this->primary_key});
      $class      = String::camelize($attribute);
      $record     = new $class();
      return $this->$attribute = $record->find(':first', array('conditions' => &$conditions));
    }
    
    # association: has many
    elseif (array_key_exists($attribute, $this->has_many))
    {
      $conditions = array($this->has_many[$attribute]['foreign_key'] => $this->{$this->primary_key});
      $class      = String::camelize(String::singularize($attribute));
      $record     = new $class();
      return $this->$attribute = $record->find(':all', array('conditions' => &$conditions));
    }
    
    # another
    return parent::__get($attribute);
  }
  
  /**
   * Finds records in database.
   * 
   * Scopes:
   *   - :all
   *   - :first
   * 
   * Options:
   *   - select (collection)
   *   - conditions (string, array or hash)
   *   - group (collection)
   *   - order (collection)
   *   - limit (integer)
   *   - page (integer)
   * 
   * TODO: Test option 'group'.
   * IMPROVE: Add scope :last (how is that doable?).
   */
  function & find($scope=':all', $options=null)
  {
    # arguments
    if (!is_symbol($scope))
    {
      if (is_array($scope) and !is_array($options))
      {
        $options =& $scope;
        $scope = ':all';
      }
      else
      {
        $options = array(
          'conditions' => array($this->primary_key => $scope),
        );
        $scope = ':first';
      }
    }
    
    # optimization(s)
    if ($scope == ':first' and !isset($options['limit'])) {
      $options['limit'] = 1;
    }
    
    # buils SQL
    $table  = $this->db->quote_table($this->table_name);
    $select = empty($options['select']) ? '*' : $this->db->quote_columns($options['select']);
    $where  = '';
    $group  = '';
    $order  = '';
    $limit  = '';
    $joins  = '';
    
    if (!empty($options['joins'])) {
      $joins = is_array($options['joins']) ? implode(' ', $options['joins']) : $options['joins'];
    }
    if (!empty($options['conditions'])) {
      $where = 'WHERE '.$this->db->sanitize_sql_for_conditions($options['conditions']);
    }
    if (!empty($options['group'])) {
      $group = 'GROUP BY '.$this->db->sanitize_order($options['group']);
    }
    if (!empty($options['order'])) {
      $order = 'ORDER BY '.$this->db->sanitize_order($options['order']);
    }
    if (isset($options['limit']))
    {
      $page  = isset($options['page']) ? $options['page'] : null;
      $limit = $this->db->sanitize_limit($options['limit'], $page);
    }
    
    $sql = "SELECT $select FROM $table $joins $where $group $order $limit ;";
    
    # queries then creates objects
    $class = get_class($this);
    switch($scope)
    {
      case ':all':
        $results = $this->db->select_all($sql);
        $records = array();
        foreach($results as $result)
        {
          $record = new $class($result);
          $record->new_record = false;
          $records[] = $record;
        }
        return $records;
      break;
      
      case ':first':
        $result = $this->db->select_one($sql);
        if ($result)
        {
          $record = new $class($result);
          $record->new_record = false;
        }
        else {
          $record = null;
        }
        return $record;
      break;
    }
  }
  
  /**
   * Shortcut for find(:all).
   */
  function all($options=null)
  {
    return $this->find(':all', $options);
  }
  
  /**
   * Shortcut for find(:first).
   */
  function first($options=null)
  {
    return $this->find(':first', $options);
  }
  
  
  /**
   * Creates or updates the record.
   */
  function save()
  {
    $method = $this->new_record ? '_create' : '_update';
    return (bool)$this->$method();
  }
  
  protected function _create()
  {
    # timestamps
    if (array_key_exists('created_at', $this->columns))
    {
      $time = new Time(null, 'datetime');
      $this->created_at = $time->to_query();
    }
    if (array_key_exists('created_on', $this->columns))
    {
      $time = new Time(null, 'date');
      $this->created_on = $time->to_query();
    }
    
    # create
    $id = $this->db->insert($this->table_name, $this->__attributes, $this->primary_key);
    if ($id)
    {
      $this->new_record = false;
      return $this->{$this->primary_key} = $id;
    }
    return false;
  }
  
  protected function _update($attributes=null)
  {
    if ($attributes !== null)
    {
      foreach($attributes as $field => $value) {
        $this->$field = $value;
      }
    }
    
    # timestamps
    if (array_key_exists('updated_at', $this->columns))
    {
      $time = new Time(null, 'datetime');
      $this->updated_at = $time->to_query();
      if ($attributes !== null) {
        $attributes['updated_at'] = $this->updated_at;
      }
    }
    if (array_key_exists('updated_on', $this->columns))
    {
      $time = new Time(null, 'date');
      $this->updated_on = $time->to_query();
      if ($attributes !== null) {
        $attributes['updated_on'] = $this->updated_on;
      }
    }
    
    # update
    $conditions = array($this->primary_key => $this->{$this->primary_key});
    $updates    = ($attributes === null) ? $this->__attributes : $attributes;
    return $this->db->update($this->table_name, $updates, $conditions);
  }
  
  /**
   * Creates a new record.
   * 
   * <code>
   * $user  = $user->create(array('name' => 'John'));
   * $users = $user->create(array('name' => 'Jane'), array('name' => 'Billy'));
   * </code>
   * 
   * IMPROVE: Use transactions when creating multiple records.
   */
  function create(array $attributes)
  {
    if (func_num_args() > 1)
    {
      $args    = func_get_args();
      $records = array();
      
      foreach($args as $attributes) {
        $records[] = $this->create($attributes);
      }
      return $records;
    }
    else
    {
      $class  = get_class($this);
      $record = new $class($attributes);
      if ($record->_create()) {
        return $record;
      }
    }
    return false;
  }
  
  /**
   * Updates one or many records.
   * 
   * <code>
   * # update one row
   * $user = $user->update(1, array('name' => 'Joe'));
   *
   * # update many rows
   * $people = array(1 => array('name' => 'Polly'), 1 => array('name' => 'Jean'));
   * $users = $user->update(array_keys($people), array_values($people));
   * </code>
   * 
   * FIXME: Use ActiveRecord::Base::_update() for actual saving.
   * IMPROVE: Record must be loaded before it is updated, and only *changed attributes* must be recorded.
   * IMPROVE: Use transactions when updating multiple records.
   */
  function update($id, $attributes)
  {
    if (!is_array($id))
    {
      if (array_key_exists('updated_at', $this->columns))
      {
        $time = new Time(null, 'datetime');
        $attributes['updated_at'] = $time->to_query();
      }
      if (array_key_exists('updated_on', $this->columns))
      {
        $time = new Time(null, 'date');
        $attributes['updated_on'] = $time->to_query();
      }
      
      $conditions = array($this->primary_key => $id);
      if ($this->db->update($this->table_name, $attributes, $conditions) !== false)
      {
        $class = get_class($this);
        return new $class($id);
      }
      return false;
    }
    else
    {
      $records = array();
      $i = 0;
      foreach($id as $_id)
      {
        $rs = $this->update($_id, $attributes[$i]);
        
        if ($rs === false) {
          return false;
        }
        $records[] = $rs;
        $i++;
      }
      return $records;
    }
  }
  
  /**
   * Updates one attribute of record.
   *
   * <code>
   * $post = new Post(1);
   * $post->name = 'my first post [update]';
   * $post->update_attribute('name');
   * 
   * $post->update_attribute('name', 'my first post [update 2]');
   * </code>
   */
  function update_attribute($attribute, $value=null)
  {
    $value   = (func_num_args() > 1) ? $value : $this->$attribute;
    $updates = array($attribute => $value);
    return $this->update_attributes(&$updates);
  }
  
  /**
   * Updates some attributes of record.
   * 
   * <code>
   * $post = new Post(1);
   * $post->title    = 'my first post [update]';
   * $post->category = 2;
   * $post->update_attributes(array('title', 'category'));
   * $post->update_attributes('title, category');
   * 
   * $post->update_attributes(array(
   *   'title'    => 'my first post [update 2]',
   *   'category' => 3
   * ));
   * </code>
   */
  function update_attributes($updates)
  {
    # hash of fields => values
    if (is_hash($updates)) {
      return $this->_update($updates);
    }
    
    # list of fields
    if (is_string($updates)) {
      $updates = explode(',', str_replace(' ', '', $updates));
    }
    $_updates = array();
    foreach($updates as $attribute) {
      $_updates[$attribute] = $this->$attribute;
    }
    return $this->_update(&$_updates);
  }
  
  /**
   * Updates many records at once.
   * 
   * Available options:
   *   - limit
   *   - order
   */
  function update_all($updates, $conditions=null, $options=null)
  {
    return $this->db->update($this->table_name, $updates, $conditions, $options);
  }
  
  /**
   * Deletes a record.
   * 
   * <code>
   * # deletes a given record
   * $post->delete(123);
   * 
   * # deletes current record
   * $post = new Post(456);
   * $post->delete();
   * </code>
   * 
   * FIXME: Check for record's existence before deleting!
   */
  function delete($id=null)
  {
    $conditions = array($this->primary_key => isset($id) ? $id : $this->{$this->primary_key});
    return $this->db->delete($this->table_name, $conditions);
  }
  
  /**
   * Deletes many records at once.
   * 
   * Available options:
   *   - limit
   *   - order
   */
  function delete_all($conditions=null, $options=null)
  {
    return $this->db->delete($this->table_name, $conditions, $options);
  }
}

?>
