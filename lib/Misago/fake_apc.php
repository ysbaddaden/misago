<?php
# Emulates APC caching features when APC is missing.

$GLOBALS['__fake_apc_data'] = array();


# Caches a variable in the data store, only if it's not already stored. 
# :nodoc:
function apc_add($key, $var, $ttl=0)
{
  if (!array_key_exists($key, $GLOBALS['__fake_apc_data'])) {
    return apc_store($key, $var, $ttl);
  }
  return false;
}

# Caches a variable in the data store. 
# :nodoc:
function apc_store($key, $var=null, $ttl=0)
{
  if (is_array($key))
  {
    foreach($key as $k => $v) {
      $GLOBALS['__fake_apc_data'][$k] = $v;
    }
  }
  else {
    $GLOBALS['__fake_apc_data'][$key] = $var;
  }
  return true;
}

# Fetches a stored variable from the cache.
# :nodoc:
function apc_fetch($key, &$success=null)
{
  if (is_array($key))
  {
    $rs     = array();
    foreach($key as $k)
    {
      if (isset($GLOBALS['__fake_apc_data'][$k])) {
        $rs[$k] = $GLOBALS['__fake_apc_data'][$k];
      }
    }
    $sucess = true;
    return $rs;
  }
  elseif (isset($GLOBALS['__fake_apc_data'][$key]))
  {
    $success = true;
    return $GLOBALS['__fake_apc_data'][$key];
  }
  return $success = false;
}

# Removes a stored variable from the cache.
# :nodoc:
function apc_delete($key)
{
  if (is_array($key))
  {
    foreach($key as $k) {
      unset($GLOBALS['__fake_apc_data'][$k]);
    }
  }
  else {
    unset($GLOBALS['__fake_apc_data'][$key]);
  }
  return true;
}

# Clears user cache.
# :nodoc:
function apc_clear_cache()
{
  $GLOBALS['__fake_apc_data']    = array();
  return true;
}

?>
