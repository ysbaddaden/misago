<?php

# Handles database migrations.
class ActiveRecord_Migration
{
  # Database Object.
  protected $connection;
  
  # Alias for +$connection+.
  protected $db;
  protected $version;
  
  function __construct($version)
  {
    $this->connection = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    $this->db = $this->connection;
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
  
  # Returns the full list of migrations.
  static function migrations()
  {
    $files = glob(ROOT.'/db/migrate/*.php');
    sort($files);
    
    $migrations = array();
    foreach($files as $file)
    {
      preg_match('/^(\d+)_(.+)$/', basename($file), $match);
      $migrations[$match[1]] =array(
        'version' => $match[1],
        'file'    => $file,
        'class'   => String::singularize(String::camelize(str_replace('.php', '', $match[2]))),
      );
    }
    return $migrations;
  }
  
  # Runs a particular migration.
  static function run($migration, $direction='up')
  {
    require_once $migration['file'];
    $class = $migration['class'];
    
    $obj = new $class($migration['version']);
    $rs = $obj->migrate($direction);
    
    if ($rs)
    {
      switch ($direction)
      {
        case 'up': $version = $migration['version']; break;
        case 'down':
          $version = 0;
          foreach(array_keys(ActiveRecord_Migration::migrations()) as $v)
          {
            if ($v == $migration['version']) {
              break;
            }
            $version = $v;
          }
        break;
      }
      ActiveRecord_Migration::save_version($version);
    }
    else {
      throw new Exception("An error occured.");
    }
  }
  
  
  # Runs migration in the given direction (either up or down).
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
