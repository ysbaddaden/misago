<?php

$GLOBALS['_misago_cfg_data'] = array();

# Sets a config variable, usable throught the whole framework.
# It uses the same syntax as PHP's ini_set().
# 
# :namespace: Config
function cfg_set($var, $value) {
  return $GLOBALS['_misago_cfg_data'][$var] = $value;
}

# Gets a previously set config variable. If the variable hasn't been set
# it returns $default if present, and null otherwise.
# 
# :namespace: Config
function cfg_get($var, $default=null)
{
  return isset($GLOBALS['_misago_cfg_data'][$var]) ?
    $GLOBALS['_misago_cfg_data'][$var] : $default;
}

# Returns true if a config variable has been set, false otherwise.
# 
# :namespace: Config
function cfg_isset($var) {
  return isset($GLOBALS['_misago_cfg_data'][$var]);
}

?>
