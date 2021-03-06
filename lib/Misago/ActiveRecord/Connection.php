<?php
namespace Misago\ActiveRecord;
require_once 'Misago/ActiveRecord/Exception.php';

# Handles database connections.
class Connection
{
  private static $configurations;
  private static $adapters = array();
  
  # Loads configurations from +config/database.yml+.
  static function load_configuration()
  {
    $apc_key = TMP.'/cache/database.serialized.php';
    self::$configurations = apc_fetch($apc_key, $success);
    if ($success === false)
    {
      $configurations = file_get_contents(ROOT.'/config/database.yml');
      self::$configurations = yaml_decode($configurations);
      apc_store($apc_key, self::$configurations);
    }
  }
  
  # Creates a singleton (one single connection object per configuration entry).
  # 
  # Shouldn't be called directly, except on a few circumstances.
  # Use +get()+ instead.
  static function create($environment)
  {
    if (!isset(self::$configurations)) {
      self::load_configuration();
    }
    if (!isset(self::$configurations[$environment])) {
      throw new ConfigurationError("No such configuration: $environment.");
    }
    
    $config = self::$configurations[$environment];
    
    if (empty($config['adapter'])) {
      throw new AdapterNotSpecified("Adapter not specified in configuration: $environment.");
    }
    
    $class = "Misago\ActiveRecord\ConnectionAdapters\\".\Misago\ActiveSupport\String::camelize($config['adapter']).'Adapter';
    return new $class($config);
  }
  
	# Returns the connection object for the given configuration.
  # Will create it automatically, if it doesn't exists already.
  static function get($environment)
  {
    if (!isset(self::$adapters[$environment]))
    {
      self::$adapters[$environment] = self::create($environment);
      if (!self::$adapters[$environment]->is_active()) {
        self::$adapters[$environment]->select_database();
      }
    }
    return self::$adapters[$environment];
  }
}

?>
