<?php
/**
 * 
 * @package ActiveRecord
 * 
 * TODO: Implement has_one association.
 * TODO: Implement belongs_to association.
 * TODO: Implement has_many association.
 * TODO: Implement has_and_belongs_to_many association.
 * 
 * TODO: Implement calculations.
 */
class ActiveRecord_Base extends ActiveRecord_Record
{
  protected $db;
  protected $table_name;
  protected $primary_key = 'id';
  protected $columns     = array();
  
  protected $new_record  = true;
  
  protected $belongs_to = array();
  protected $has_one    = array();
  protected $has_many   = array();
  
  
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
   */
  protected function set_attributes($arg)
  {
    foreach($arg as $attribute => $value) {
      $this->$attribute = $value;
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
    if (in_array($attribute, array_keys($this->columns)))
    {
      # field
      return parent::__get($attribute);
    }
    elseif (in_array($attribute, $this->belongs_to))
    {
      # association: belongs to
      $foreign_key = "{$attribute}_id";
      $conditions  = array('id' => $this->{$foreign_key});
      $class       = String::camelize($attribute);
      
      $record = new $class();
      return $this->$attribute = $record->find(':first', array('conditions' => &$conditions));
    }
    elseif (in_array($attribute, $this->has_one))
    {
      # association: has one
      $foreign_key = String::underscore(get_class($this))."_id";
      $conditions  = array($foreign_key => $this->{$this->primary_key});
      $class       = String::camelize($attribute);
      
      $record = new $class();
      return $this->$attribute = $record->find(':first', array('conditions' => &$conditions));
    }
    elseif (in_array($attribute, $this->has_many))
    {
      # association: has many
      $foreign_key = String::underscore(get_class($this))."_id";;
      $conditions  = array($foreign_key => $this->{$this->primary_key});
      $class       = String::camelize(String::singularize($attribute));
      
      $record = new $class();
      return $this->$attribute = $record->find(':all', array('conditions' => &$conditions));
    }
  }
  
  /**
   * Finds records in database.
   * 
   * Scopes:
   *   - :last,
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
   * TODO: Add some options: joins, from.
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
    
    $sql = "SELECT $select FROM $table $where $group $order $limit ;";
    
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
   * IMPROVE: Record must be loaded before it is updated, and only *changed attributes* must be recorded.
   * FIXME: Use ActiveRecord::Base::_update() for actual saving.
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
