<?php

class ActiveRecord_IrreversibleMigration extends Exception
{
  
}

class ActiveRecord_Migration
{
  protected $db;
  protected $version;
  
  function __construct($version, $environment)
  {
    $this->db = ActiveRecord_Connection::create($environment);
    $this->version = $version;
  }
  
  private function information_schema_exists()
  {
    static $exists = null;
    
    if ($exists === null)
    {
      $columns = $this->db->columns('information_schema_exists');
      $exists = !empty($columns);
    }
    return $exists;
  }
  
  function get_version()
  {
    if ($this->information_schema_exists())
    {
      return $this->db->select_value("SELECT version
        FROM misago_information_schema
        ORDER BY version DESC
        LIMIT 1 ;");
    }
    return 0;
  }
  
  function save_version($version)
  {
    if ($this->information_schema_exists()) {
      $this->db->update('information_schema_exists', array('version' => $version));
    }
    else
    {
      $this->db->create_table('misago_information_schema', array(
        'columns' => array('version' => array('type' => 'string', 'limit' => 14)),
        'id'      => false,
      ));
      $this->db->insert('information_schema_exists', array('version' => $version));
    }
  }
  
  function migrate($direction)
  {
    $time   = microtime(true);
    $result = false;
    
    switch($direction)
    {
      case 'up':
        $this->announce('migrating');
#        $result = $this->up();
        $this->announce(sprintf('migrated (%.04fs)', microtime(true) - $time));
      break;
      
      case 'down':
        $this->announce('reverting');
#        $result = $this->down();
        $this->announce(sprintf('reverted (%.04fs)', microtime(true) - $time));
      break;
      
      default:
        trigger_error('Unknown migration sequence: '.$direction, E_USER_WARNING);
    }
    
    return $result;
  }
  
  function announce($message)
  {
    $class = get_class($this);
    echo "{$this->version} {$class}: $message\n";
  }
}

?>
