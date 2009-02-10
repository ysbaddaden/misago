<?php
/**
 * Handles database connections.
 * 
 * @package ActiveRecord
 * @subpackage Connection
 */
class ActiveRecord_Connection
{
  public  static $configurations;
  private static $adapters = array();
  
  /**
   * Loads configurations from config/database.yml.
   * 
   * OPTIMIZE: Cache decoded YAML database configuration in memory (using APC for instance).
   */
  static function load_configuration()
  {
    $configurations = file_get_contents(ROOT.'/config/database.yml');
    self::$configurations = Yaml::decode($configurations);
  }
  
  /**
   * Creates a singleton (one single connection object per configuration entry).
   */
  static function create($environment)
  {
    if (!isset(self::$configurations)) {
      self::load_configuration();
    }
    
    if (!isset(self::$configurations[$environment];)) {
      throw new ActiveRecord_ConfigurationError("No such configuration: $environment.");
    }
    
    $config = self::$configurations[$environment];
    
    if (empty($config['adapter'])) {
      throw new ActiveRecord_AdapterNotSpecified("Adapter not specified in configuration: $environment.");
    }
    
    # FIXME: file_exists must search in INC path.
#    if (!file_exists('active_record/connection_adapters/'.String::underscore().'_adapter.php', true) {
#      throw new ActiveRecord_AdapterNotFound("Adapter {$config['adapter']} can't be found.");
#    }
    
    $class  = "ActiveRecord_ConnectionAdapters_".String::camelize($config['adapter']).'Adapter';
    return new $class(&$config);
  }
  
  /**
   * Returns the connection object for the given configuration.
   * Will create it automatically, if it doesn't exists already.
   */
  static function get($environment)
  {
    if (!isset(self::$adapters[$environment])) {
      self::$adapters[$environment] = self::create($environment);
    }
    return self::$adapters[$environment];
  }
}

?>
