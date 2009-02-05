<?php

# TODO: Add support for foreign keys {:references => '', :on_update => '', :on_delete => ''}
class ActiveRecord_Table
{
  private  $db;
  
  public   $name;
  public   $columns = array();
  public   $definitions = array(
    'id'          => true,
    'primary_key' => 'id',
  );
  
  function __construct($name, array $definitions=null, ActiveRecord_ConnectionAdapters_AbstractAdapter $db)
  {
    $this->db   = $db;
    $this->name = $name;
    
    if (!empty($definitions)) {
      $this->definitions = array_merge($this->definitions, $definitions);
    }
    if (isset($this->definitions['id']) and $this->definitions['id']) {
      $this->add_column('primary_key', $this->definitions['primary_key']);
    }
  }
  
  function add_column($type, $name, array $options=null)
  {
    $definition = array(
      'type' => $type,
    );
    if (!empty($options)) {
      $definition = array_merge($definition, $options);
    }
    
    if (isset($this->columns[$name])) {
      trigger_error("Column $name already defined (overwriting it).", E_USER_WARNING);
    }
    
    $this->columns[$name] = $definition;
  }
  
  function add_timestamps($type='datetime')
  {
    switch($type)
    {
      case 'date':
        $type = $this->db->sql_type_for('date');
        $this->add_column($type, 'created_on');
        $this->add_column($type, 'updated_on');
      break;
      
      case 'time':
        $type = $this->db->sql_type_for('date');
        $this->add_column($type, 'created_at');
        $this->add_column($type, 'updated_at');
      break;
      
      case 'datetime':
      default:
        $type = $this->db->sql_type_for('datetime');
        $this->add_column($type, 'created_at');
        $this->add_column($type, 'updated_at');
    }
  }
  
  function create()
  {
    $this->definitions['columns'] =& $this->columns;
    return $this->db->create_table($this->name, $this->definitions);
  }
}

?>
