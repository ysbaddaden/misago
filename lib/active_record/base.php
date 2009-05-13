<?php
/**
 * 
 * @package ActiveRecord
 * 
 * TODO: Implement calculations.
 */
abstract class ActiveRecord_Base extends ActiveRecord_Validations
{
  protected $db;
  protected $table_name;
  protected $primary_key = 'id';
  protected $columns     = array();
  
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
    ActiveRecord_Associations::__construct();
    
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
  
  /**
   * Declares some magic methods.
   * 
   * Examples:
   * 
   *   $user  = $user->find_by_id();
   *   $post  = $post->find_first_by_tag($tag);
   *   $posts = $post->find_all_by_category_id($category_id);
   */
  function __call($name, $args)
  {
    if (preg_match('/^find(?:_([^_]+)|)(?:_by_(.+)|)$/', $name, $match))
    {
      if (!empty($match[2]))
      {
        if (!in_array($match[2], array_keys($this->columns)))
        {
          trigger_error("No such column '{$match[2]}'.", E_USER_WARNING);
          return;
        }
        if (!isset($args[0]))
        {
          trigger_error("Missing parameter: 'value'.", E_USER_WARNING);
          return;
        }
        $options = isset($args[1]) ? $args[1] : array();
        $options['conditions'] = array($match[2] => $args[0]);
      }
      else {
        $options = isset($args[0]) ? $args[0] : array();
      }
      
      $scope = empty($match[1]) ? ':first' : ':'.$match[1];
      return $this->find($scope, $options);
    }
    
    $class = get_class($this);
    trigger_error("No such method: $class::$name().", E_USER_ERROR);
  }
  
  /**
   * Finds records in database.
   * 
   * Scopes:
   * 
   *   :all    Returns all found records.
   *   :first  Returns the first found record.
   *   :values Returns bare values (uninstanciated).
   * 
   * Options:
   * 
   *   - select (collection)
   *   - conditions (string, array or hash)
   *   - group (collection)
   *   - order (collection)
   *   - limit (integer)
   *   - page (integer)
   *   - include (collection)
   * 
   * Eager Loading:
   * 
   * See documentation of ActiveRecord_Associations.
   * 
   * TODO: Test option 'group'.
   */
  function find($scope=':all', $options=null)
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
    
    # queries then creates objects
    $sql = $this->build_sql_from_options(&$options);
    
    $model = get_class($this);
    switch($scope)
    {
      case ':all':
        $results = $this->db->select_all($sql);
        $records = array();
        foreach($results as $result)
        {
          $record = new $model($result);
          $record->new_record = false;
          $records[] = $record;
        }
        $records = new ActiveRecord_Collection($records, $model);
        if (!empty($options['include'])) {
          $this->eager_loading($records, $options['include']);
        }
        return $records;
      break;
      
      case ':first':
        $result = $this->db->select_one($sql);
        if ($result)
        {
          $record = new $model($result);
          $record->new_record = false;
        }
        else {
          $record = null;
        }
        return $record;
      break;
      
      case ':values':
        $results = $this->db->select_all($sql);
        foreach($results as $i => $values) {
          $results[$i] = array_values($results[$i]);
        }
        return $results;
      break;
    }
  }
  
  protected function build_sql_from_options($options)
  {
    # builds SQL
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
    
    return "SELECT $select FROM $table $joins $where $group $order $limit ;";
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
   * Shortcut for find(:values).
   */
  function values($options=null)
  {
    return $this->find(':values', $options);
  }
  
  /**
   * Checks wether a given record exists or not.
   */
  function exists($id)
  {
    if (empty($id) and strlen($id) == 0) {
      return false;
    }
    $options = array(
      'conditions' => array($this->primary_key => $id),
      'select'     => $this->primary_key,
    );
    $self = $this->find(':first', $options);
    return (gettype($self) == 'object');
  }
  
  /**
   * Creates or updates the record.
   */
  function save()
  {
    $method = $this->new_record ? '_create' : '_update';
    return (bool)$this->$method();
  }
  
  # Generic create record method.
  # 
  # You better consider this method as private.
  # Do not use unless you know what you are doing (ie. you're hacking misago).
  # Use ActiveRecord_Base::create() instead.
  protected function _create()
  {
    $this->before_save();
    $this->before_create();
    
    if (!$this->is_valid()) {
      return false;
    }
    
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
      $this->{$this->primary_key} = $id;
      
      $this->after_save();
      $this->after_create();
      
      return $id;
    }
    return false;
  }
  
  # Generic update record method.
  # 
  # You better consider this method as private.
  # Do not use unless you know what you are doing (ie. you're hacking misago).
  # Use ActiveRecord_Base::update() or ActiveRecord_Base::update_attributes() instead.
  protected function _update($attributes=null)
  {
    if ($attributes !== null)
    {
      foreach($attributes as $field => $value) {
        $this->$field = $value;
      }
    }
    
    $this->before_save();
    $this->before_update();
    
    if (!$this->is_valid()) {
      return false;
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
    
    $rs = $this->db->update($this->table_name, $updates, $conditions);
    
    if ($rs !== false)
    {
      $this->after_save();
      $this->after_update();
      return $rs;
    }
    return false;
  }
  
  /**
   * Creates a new record.
   * 
   *   $user  = $user->create(array('name' => 'John'));
   *   $users = $user->create(array('name' => 'Jane'), array('name' => 'Billy'));
   */
  function create(array $attributes)
  {
    if (func_num_args() == 1)
    {
      $class  = get_class($this);
      $record = new $class($attributes);
      $record->_create();
      
      return $record;
    }
    else
    {
      $args    = func_get_args();
      $records = array();
      
      $this->db->transaction('begin');
      
      foreach($args as $attributes)
      {
        $record = $this->create($attributes);
        if ($record === false)
        {
          $this->db->transaction('rollback');
          return false;
        }
        $records[] = $record;
      }
      
      $this->db->transaction('commit');
      return $records;
    }
    return false;
  }
  
  /**
   * Updates one or many records.
   * 
   *   # update one row
   *   $user = $user->update(1, array('name' => 'Joe'));
   *   
   *   # update many rows
   *   $people = array(
   *     1 => array('name' => 'Polly'),
   *     2 => array('name' => 'Jean')
   *   );
   *   $users = $user->update(array_keys($people), array_values($people));
   */
  function update($id, $attributes)
  {
    if (!is_array($id))
    {
      $class = get_class($this);
      
      $record = new $class($id);
      $record->set_attributes($attributes);
      $attributes = array_intersect_key($attributes, $this->columns);
      $record->_update($attributes);
      
      return $record;
    }
    else
    {
      $records = array();
      $i = 0;
      
      $this->db->transaction('begin');
      
      foreach($id as $_id)
      {
        $rs = $this->update($_id, $attributes[$i]);
        
        if ($rs === false)
        {
          $this->db->transaction('rollback');
          return false;
        }
        $records[] = $rs;
        $i++;
      }

      $this->db->transaction('commit');
      return $records;
    }
  }
  
  /**
   * Updates one attribute of record.
   * 
   *   $post = new Post(1);
   *   $post->name = 'my first post [update]';
   *   $post->update_attribute('name');
   *   
   *   $post->update_attribute('name', 'my first post [update 2]');
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
   *   $post = new Post(1);
   *   $post->title    = 'my first post [update]';
   *   $post->category = 2;
   *   $post->update_attributes(array('title', 'category'));
   *   $post->update_attributes('title, category');
   *   
   *   $post->update_attributes(array(
   *     'title'    => 'my first post [update 2]',
   *     'category' => 3
   *   ));
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
   * 
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
   *   # deletes a given record
   *   $post->delete(123);
   *   
   *   # deletes current record
   *   $post = new Post(456);
   *   $post->delete();
   */
  function delete($id=null)
  {
    $id = isset($id) ? $id : $this->{$this->primary_key};
    
    if ($this->exists($id))
    {
      $class = get_class($this);
      $self  = new $class($id);
      $self->before_delete();
      
      $conditions = array($this->primary_key => $id);
      if (!$this->db->delete($this->table_name, $conditions)) {
        return false;
      }

      $self->after_delete();
      return true;
    }
  }
  
  /**
   * Deletes many records at once.
   * 
   * Each matching record are instanciated, and deletion callbacks are run.
   * 
   * Available options:
   * 
   *   - limit
   *   - order
   */
  function delete_all($conditions=null, $options=null)
  {
    if (!empty($conditions)) {
      $options['conditions'] = $conditions;
    }
    
    $sql = $this->build_sql_from_options($options);
    $ids = $this->db->select_values($sql);
    
    foreach($ids as $id)
    {
      if (!$this->delete($id[0])) {
        return false;
      }
    }
    return true;
  }
  
  # Destroys a record.
  # 
  # Record isn't instanciated, and deletion callbacks aren't run.
  function destroy($id=null)
  {
    if ($id === null) {
      $id = $this->{$this->primary_key};
    }
    $conditions = array($this->primary_key => $id);
    return $this->db->delete($this->table_name, $conditions);
  }
  
  # Destroys many records at once.
  # 
  # Records aren't instanciated, and deletion callbacks aren't run.
  function destroy_all($conditions=null, $options=null)
  {
    return $this->db->delete($this->table_name, $conditions, $options);
  }
  
  # TODO: Test before_* and after_* callbacks.
  protected function before_save()   {}
  protected function after_save()    {}
  
  protected function before_create() {}
  protected function after_create()  {}
  
  protected function after_update()  {}
  protected function before_update() {}

  protected function before_delete() {}
  protected function after_delete()  {}
}

?>
