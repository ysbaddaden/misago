<?php
/**
 * Helper to create new tables. Generally used in migrations,
 * but permits to create temporary tables too.
 * 
 * IMPROVE: Add support for foreign keys {:references => '', :on_update => '', :on_delete => ''}
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
      $this->add_column($this->definitions['primary_key'], 'primary_key');
    }
  }
  
  /**
   * Adds a columns to table's definition.
   */
  function add_column($column, $type, array $options=null)
  {
    $definition = array(
      'type' => $type,
    );
    if (!empty($options)) {
      $definition = array_merge($definition, $options);
    }
    
    if (isset($this->columns[$column])) {
      trigger_error("Column $column already defined (overwriting it).", E_USER_WARNING);
    }
    
    $this->columns[$column] = $definition;
  }
  
  /**
   * Adds timestamp columns to table's definition.
   * 
   * $type can be:
   * 
   * - date: will add created_on & updated_on.
   * - time: will add created_at & updated_at.
   * - datetime: will add created_at & updated_at.
   */
  function add_timestamps($type='datetime')
  {
    switch($type)
    {
      case 'date':
        $this->add_column('created_on', $type);
        $this->add_column('updated_on', $type);
      break;
      
      case 'time':
      case 'datetime':
      default:
        $this->add_column('created_at', $type);
        $this->add_column('updated_at', $type);
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
