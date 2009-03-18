<?php
/**
 * 
 * @package ActiveRecord
 * 
 * TODO: Implement eager loading (:include => 'assoc').
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
   * find_all_by_category();
   * find_by_id() or find_first_by_id();
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
    
    # queries then creates objects
    $sql = $this->build_sql_from_options(&$options);
    
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
    if (func_num_args() == 1)
    {
      $class  = get_class($this);
      $record = new $class($attributes);
      
      if (!$record->is_valid()) {
        return $record;
      }
      if ($record->_create()) {
        return $record;
      }
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
   * <code>
   * # update one row
   * $user = $user->update(1, array('name' => 'Joe'));
   *
   * # update many rows
   * $people = array(1 => array('name' => 'Polly'), 1 => array('name' => 'Jean'));
   * $users = $user->update(array_keys($people), array_values($people));
   * </code>
   */
  function update($id, $attributes)
  {
    if (!is_array($id))
    {
      $class = get_class($this);
      
      $record = new $class($id);
      $record->set_attributes($attributes);
      
      if (!$record->is_valid()) {
        return $record;
      }
      return ($record->_update($attributes) !== false) ? $record : false;
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
    $id = isset($id) ? $id : $this->{$this->primary_key};
    if ($this->exists($id))
    {
      $conditions = array($this->primary_key => $id);
      return $this->db->delete($this->table_name, $conditions);
    }
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
