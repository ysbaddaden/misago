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
  
  
  # TODO: Post(:id)
  function __construct($arg=null)
  {
    # database connection & relation
    $this->table_name = String::underscore(String::pluralize(get_class($this)));
    $this->db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    $this->db->select_database();
    $this->columns = $this->db->columns($this->table_name);
    
    # args
    if (is_array($arg)) {
      $this->set_attributes($arg);
    }
  }
  
  function set_attributes(array $arg)
  {
    foreach($arg as $attribute => $value) {
      $this->$attribute = $value;
    }
  }
  
  function __set($attr, $value)
  {
    if (isset($this->columns[$attr]))
    {
      if ($this->columns[$attr]['type'] == 'integer') {
        $value = (int)$value;
      }
      elseif ($this->columns[$attr]['type'] == 'float') {
        $value = (float)$value;
      }
    }
    return parent::__set($attr, $value);
  }
  
  /**
   * TODO: find(:all, $options)
   * TODO: find($options)
   * TODO: find(:first, $options)
   * 
   * OPTIMIZE: if $scope is :first add {limit => 1} to options.
   */
  function & find($scope=':all')
  {
    $table   = $this->db->quote_table($this->table_name);
    $results = $this->db->select_all("SELECT * FROM {$table} ;");
    
    $class   = get_class($this);
    switch($scope)
    {
      case ':all':
        $records = array();
        foreach($results as $result) {
          $records[] = new $class($result);
        }
        return $records;
      break;
      
      case ':first':
        $record = isset($results[0]) ? new $class($results[0]) : null;
        return $record;
      break;
    }
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
}

?>
