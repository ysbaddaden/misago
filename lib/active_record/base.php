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
  
  function create()
  {
    $id = $this->db->insert($this->table_name, $this->__attributes, $this->primary_key);
    if ($id)
    {
      $this->{$this->primary_key} = $id;
      return true;
    }
    return false;
  }
}

?>
