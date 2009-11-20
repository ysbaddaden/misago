<?php

# Handles configuration across the framework and application.
# TODO: use cfg_set() and cfg_get() functions instead of cfg class.
# :nodoc:
class cfg
{
  static public $data = array();
  
  # Gets a config variable if it exists, otherwise returns +$default+.
  static function get($var, $default=null)
  {
    trigger_error("Use cfg_get() instead.", E_USER_DEPRECATED);
    return isset(cfg::$data[$var]) ? cfg::$data[$var] : $default;
  }
  
  # Sets a config variable.
  static function set($var, $value)
  {
    trigger_error("Use cfg_set() instead.", E_USER_DEPRECATED);
    return cfg::$data[$var] = $value;
  }
  
  # Checks wether the config variable is set or not.
  static function is_set($var)
  {
    trigger_error("Use cfg_isset() instead.", E_USER_DEPRECATED);
    return isset(cfg::$data[$var]);
  }
}

# :namespace: Config
function cfg_set($var, $value)
{
  return cfg::$data[$var] = $value;
}

# :namespace: Config
function cfg_get($var, $default=null)
{
  return isset(cfg::$data[$var]) ? cfg::$data[$var] : $default;
}

# :namespace: Config
function cfg_isset($var)
{
  return isset(cfg::$data[$var]);
}

?>
