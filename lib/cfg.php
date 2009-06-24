<?php

class cfg
{
  static protected $data = array();
  
  static function get($var)
  {
    return isset(cfg::$data[$var]) ? cfg::$data[$var] : null;
  }
  
  static function set($var, $value)
  {
    cfg::data[$var] = $value;
  }
  
  static function is_set()
  {
    return isset(cfg::$data[$var]);
  }
}

?>
