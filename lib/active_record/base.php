<?php
/**
 * 
 * @package ActiveRecord
 */
class ActiveRecord_Base extends ActiveRecord_Record
{
  protected $db;
  
  protected $table_name;
  protected $primary_key = 'id';
  protected $columns     = array();
  
  # Returns the definition of columns for the table associated with this class.
  function columns()
  {
    return $this->columns;
  }
  
  # Returns the list of column names for the table associated with this class.
  function & column_names()
  {
    $column_names = array_keys($this->table_columns);
    return $column_names;
  }
  
  # OPTIMIZE: Cache columns' definition (eg: in memory throught APC).
  function __construct($arg=null)
  {
    # database connection
    $this->table_name = String::underscore(String::pluralize(get_class($this)));
    $this->db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    $this->db->select_database();
    
    # columns' definition
    $this->columns = $this->db->columns($this->table_name);
    
    # args
    if ($arg !== null)
    {
      if (!is_array($arg)) {
        $arg = $this->find($arg);
      }
      $this->set_attributes($arg);
    }
  }
  
  function set_attributes($arg)
  {
    foreach($arg as $attribute => $value) {
      $this->$attribute = $value;
    }
  }
  
  function __set($attribute, $value)
  {
    if (isset($this->columns[$attribute]))
    {
      switch($this->columns[$attribute]['type'])
      {
        case 'integer': $value = (int)$value;    break;
        case 'double':  $value = (double)$value; break;
      }
    }
    return parent::__set($attribute, $value);
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
   *   - order (collection)
   *   - limit (integer)
   *   - page (integer)
   * 
   * TODO: Add some options: group, joins, from.
   * TODO: Add scope :last (how is that doable?).
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
    $order  = '';
    $limit  = '';
    
    if (!empty($options['conditions'])) {
      $where = 'WHERE '.$this->db->sanitize_sql_for_conditions($options['conditions']);
    }
    if (!empty($options['order'])) {
      $where = 'ORDER BY '.$this->db->sanitize_order($options['order']);
    }
    if (isset($options['limit']))
    {
      $page  = isset($options['page']) ? $options['page'] : null;
      $limit = $this->db->sanitize_limit($options['limit'], $page);
    }
    
    $sql = "SELECT $select FROM $table $where $order $limit ;";
    
    # queries then creates objects
    $class = get_class($this);
    switch($scope)
    {
      case ':all':
        $results = $this->db->select_all($sql);
        $records = array();
        foreach($results as $result) {
          $records[] = new $class($result);
        }
        return $records;
      break;
      
      case ':first':
        $result = $this->db->select_one($sql);
        $record = $result ? new $class($result) : null;
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
   * Creates a new record (saved in database).
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
   * FIXME: Must return ActiveRecord objects!
   * FIXME: Record must be loaded before it is updated, and only changed attributes must be recorded.
   */
  function update($id, $attributes)
  {
    if (is_array($id))
    {
      $records = array();
      $i = 0;
      foreach($id as $_id)
      {
        $records[$i] = $this->update($_id, $attributes[$i]);
        $i++;
      }
      return $records;
    }
    else
    {
      $conditions = array($this->primary_key => $id);
      return $this->db->update($this->table_name, $attributes, $conditions);
    }
  }
  
  private function _create()
  {
    $id = $this->db->insert($this->table_name, $this->__attributes, $this->primary_key);
    if ($id) {
      return $this->{$this->primary_key} = $id;
    }
    return false;
  }
  
  # TODO: Write ActiveRecord::Base::update_all().
  function update_all($conditions=null)
  {
    
  }
  
  # TODO: Write ActiveRecord::Base::delete().
  function delete($id=null)
  {
    
  }
  
  # TODO: Write ActiveRecord::Base::delete_all().
  function delete_all($conditions=null)
  {
    
  }
}

?>
