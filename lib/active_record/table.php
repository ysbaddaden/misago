<?php

class ActiveRecord_Table
{
  private  $db;
  
  public   $name;
  public   $columns = array();
  
  function __construct($name, array $options=null, ActiveRecord_ConnectionAdapters_AbstractAdapter $db)
  {
    $this->db   = $db;
    $this->name = $name;
    
    $defaults = array(
      'id'          => true,
      'primary_key' => 'id',
    );
    $this->options = $options ? array_merge($defaults, $options) : $defaults;
    
    if (isset($this->options['id']) and $this->options['id'])
    {
      $pk = isset($this->options['primary_key']) ? $this->options['primary_key'] : 'id';
      $this->add_column('primary_key', $pk);
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
      trigger_error("Column $name already exists.", E_USER_ERROR);
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
    $this->db->create_table($this->name, array(
      'columns' => $this->columns,
    ));
  }
}

?>
