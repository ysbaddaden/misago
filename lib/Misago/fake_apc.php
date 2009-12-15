<?php
# Emulates APC caching features when APC is missing.

$GLOBALS['__fake_apc_data'] = array();


# Caches a variable in the data store, only if it's not already stored. 
# 
# FIXME: handle array of keys.
# :nodoc:
function apc_add($key, $var, $ttl=0)
{
  if (!array_key_exists($key, $GLOBALS['__fake_apc_data'])) {
    return apc_store($key, $var, $ttl);
  }
  return true;
}

# Caches a variable in the data store. 
# 
# FIXME: handle array of keys.
# :nodoc:
function apc_store($key, $var, $ttl=0)
{
  $GLOBALS['__fake_apc_data'][$key] = $var;
  return true;
}

# Fetches a stored variable from the cache.
# 
# FIXME: handle array of keys.
# :nodoc:
function apc_fetch($key, &$success=null)
{
  if (isset($GLOBALS['__fake_apc_data'][$key]))
  {
    $success = true;
    return $GLOBALS['__fake_apc_data'][$key];
  }
  return $success = false;
}

# Removes a stored variable from the cache.
# 
# :nodoc:
function apc_delete($key)
{
  unset($GLOBALS['__fake_apc_data'][$key]);
  return true;
}

# Clears user cache.
# 
# :nodoc:
function apc_clear_cache()
{
  $GLOBALS['__fake_apc_data']    = array();
  return true;
}

?>
