<?php

$GLOBALS['_misago_cfg_data'] = array();

# :namespace: Config
function cfg_set($var, $value)
{
  return $GLOBALS['_misago_cfg_data'][$var] = $value;
}

# :namespace: Config
function cfg_get($var, $default=null)
{
  return isset($GLOBALS['_misago_cfg_data'][$var]) ?
    $GLOBALS['_misago_cfg_data'][$var] : $default;
}

# :namespace: Config
function cfg_isset($var)
{
  return isset($GLOBALS['_misago_cfg_data'][$var]);
}

?>
