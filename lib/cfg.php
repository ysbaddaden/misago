<?php

# Handles configuration across the framework and application.
class cfg
{
  static protected $data = array();
  
  # Gets a config variable if it exists, otherwise returns `$default`.
  static function get($var, $default=null)
  {
    return isset(cfg::$data[$var]) ? cfg::$data[$var] : $default;
  }
  
  # Sets a config variable.
  static function set($var, $value)
  {
    cfg::$data[$var] = $value;
  }
  
  # Checks wether the config variable is set or not.
  static function is_set($var)
  {
    return isset(cfg::$data[$var]);
  }
}

?>
