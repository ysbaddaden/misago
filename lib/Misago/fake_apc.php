<?php
# Emulates APC caching features when APC is missing.
# 
# IMPROVE: Save data on disk, to emulate between requests cache.

$GLOBALS['__fake_apc_data'] = array();
#$GLOBALS['__fake_apc_expires'] = array();


# Caches a variable in the data store, only if it's not already stored. 
# 
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
# :nodoc:
function apc_store($key, $var, $ttl=0)
{
  $GLOBALS['__fake_apc_data'][$key] = $var;
#  $GLOBALS['__fake_apc_expires'][$key] = $ttl ? time() + $ttl : strtotime('+1 year');
  return true;
}

# Fetches a stored variable from the cache.
# 
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
#  unset($GLOBALS['__fake_apc_expires'][$key]);
  return true;
}

# Clears user cache.
# 
# :nodoc:
function apc_clear_cache()
{
  $GLOBALS['__fake_apc_data']    = array();
#  $GLOBALS['__fake_apc_expires'] = array();
  return true;
}

?>