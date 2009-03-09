<?php
/**
 * Helper to create new tables. Generally used in migrations,
 * but permits to create temporary tables too.
 * 
 * @package ActiveRecord
 * 
 * TODO: Add support for foreign keys {:references => '', :on_update => '', :on_delete => ''}
 */
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
  
  /**
   * Adds a columns to table's definition.
   */
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
  
  /**
   * Adds timestamp columns to table's definition.
   * 
   * $type can be:
   *   - date, which will create created_on & updated_on.
   *   - time, which will create created_at & updated_at.
   *   - datetime, which will create created_at & updated_at.
   */
  function add_timestamps($type='datetime')
  {
    switch($type)
    {
      case 'date':
        $type = $this->db->NATIVE_DATABASE_TYPES['date'];
        $this->add_column($type['name'], 'created_on');
        $this->add_column($type['name'], 'updated_on');
      break;
      
      case 'time':
        $type = $this->db->NATIVE_DATABASE_TYPES['time'];
        $this->add_column($type['name'], 'created_at');
        $this->add_column($type['name'], 'updated_at');
      break;
      
      case 'datetime':
      default:
        $type = $this->db->NATIVE_DATABASE_TYPES['datetime'];
        $this->add_column($type['name'], 'created_at');
        $this->add_column($type['name'], 'updated_at');
    }
  }
  
  /**
   * Actually creates the table in database.
   */
  function create()
  {
    $this->definitions['columns'] =& $this->columns;
    return $this->db->create_table($this->name, $this->definitions);
  }
}

?>
