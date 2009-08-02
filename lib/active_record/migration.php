<?php

# Handles database migrations.
class ActiveRecord_Migration
{
  protected $db;
  protected $version;
  
  function __construct($version, $environment)
  {
    $this->db = ActiveRecord_Connection::get($environment);
    $this->version = $version;
  }
  
  # Checks wether the information_schema table exists in the current database.
  # It's used to store the latest migration timestamps.
  static private function information_schema_exists()
  {
    $db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    return $db->table_exists('misago_information_schema');
  }
  
  # Returns the timestamp of the last migration runned.
  static function get_version()
  {
    if (self::information_schema_exists())
    {
      $db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
      
      return $db->select_value("SELECT version
        FROM misago_information_schema
        ORDER BY version DESC
        LIMIT 1 ;");
    }
    return 0;
  }
  
  # Saves a timestamp as last runned migration.
  static function save_version($version)
  {
    $db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    
    if (self::information_schema_exists()) {
      $db->update('misago_information_schema', array('version' => $version));
    }
    else
    {
      $db->create_table('misago_information_schema', array(
        'id' => false,
        'columns' => array(
          'version' => array('type' => 'string', 'limit' => 14)
         )
      ));
      $db->insert('misago_information_schema', array('version' => $version));
    }
  }
  
  # Migrate database in the given direction (either up or down).
  function migrate($direction)
  {
    $time   = microtime(true);
    $result = false;
    
    switch($direction)
    {
      case 'up':
        $this->announce('migrating');
        $result = $this->up();
        $this->announce(sprintf('migrated (%.04fs)', microtime(true) - $time));
      break;
      
      case 'down':
        $this->announce('reverting');
        $result = $this->down();
        $this->announce(sprintf('reverted (%.04fs)', microtime(true) - $time));
      break;
      
      default:
        trigger_error('Unknown migration sequence: '.$direction, E_USER_WARNING);
    }
    
    return $result;
  }
  
  # Displays a message to the end-user.
  function announce($message)
  {
    if (!isset($_SERVER['migrate_debug']) or $_SERVER['migrate_debug'])
    {
      $class = get_class($this);
      echo "{$this->version} {$class}: $message\n";
    }
  }
}

?>
