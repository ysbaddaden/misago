<?php

require "config/database.php";

# Handles database connections.
class ActiveRecord_Connection
{
  private static $adapters = array();
  
  static function create($config_name)
  {
    if (!isset(self::$adapters[$config_name]))
    {
      $config = Database::${$config_name};
      $class  = "ActiveRecord_ConnectionAdapters_".String::camelize($config['adapter']);
      
      require_once 'active_record/connection_adapters/abstract_adapter.php';
      require 'active_record/connection_adapters/'.strtolower($config['adapter']).'_adapter.php';
      
      self::$adapters[$config_name] = new $class(&$config);
    }
    return self::$adapters[$config_name];
  }
}

?>
