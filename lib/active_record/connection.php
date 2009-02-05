<?php

# Handles database connections.
# 
# CHANGED: Dropped config/database.php in favor of config/database.yml
# OPTIMIZE: Cache decoded YAML database configuration in memory (using APC for instance).
#
class ActiveRecord_Connection
{
  public  static $configurations;
  private static $adapters = array();
  
  static function load_configuration()
  {
    $configurations = file_get_contents(ROOT.'/config/database.yml');
    self::$configurations = Yaml::decode($configurations);
  }
  
  static function create($environment)
  {
    if (!isset(self::$configurations)) {
      self::load_configurations();
    }
    
    $config = self::$configurations[$environment];
    $class  = "ActiveRecord_ConnectionAdapters_".String::camelize($config['adapter']).'Adapter';
    return new $class(&$config);
  }
  
  static function get($environment)
  {
    if (!isset(self::$adapters[$environment])) {
      self::$adapters[$environment] = self::create($environment);
    }
    return self::$adapters[$environment];
  }
}

?>
