<?php

# Handles database connections.
# 
# TODO: Cache decoded YAML database configuration in memory (using APC or memcached).
#
class ActiveRecord_Connection
{
  private static $configurations;
  private static $adapters = array();
  
  static function create($config_name)
  {
    if (!isset(self::$configurations))
    {
      $configurations = file_get_contents(ROOT.'/config/database.yml');
      self::$configurations = Yaml::decode($configurations);
    }
    
    if (!isset(self::$adapters[$config_name]))
    {
      $config = self::$configurations[$config_name];
      $class  = "ActiveRecord_ConnectionAdapters_".String::camelize($config['adapter']).'Adapter';
      self::$adapters[$config_name] = new $class(&$config);
    }
    
    return self::$adapters[$config_name];
  }
}

?>
