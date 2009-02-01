<?php

# DataBase abstraction layer.
# 
# $db = DBO::get('development');
class DBO
{
  private static $instances = array();
  
  static function get($conf)
  {
    if (!isset(self::$instances[$conf]))
    {
      $driver = Database::$$conf['driver'];
      $class  = "DBO_{$driver}";
      
      require_once 'active_record/dbo_drivers/base.php';
      require 'active_record/dbo_drivers/'.strtolower($driver).'.php';
      self::$instances[$conf] = new $class(Database::$$conf);
    }
    
    return self::$instances[$conf];
  }
  
}

?>
