<?php
namespace Misago\ActiveSupport\Cache;

# A Cache Store implementation which stores data using APC.
# See <tt>Store</tt> for help.
class MemoryStore extends Store
{
  function read($key) {
    return apc_fetch($key);
  }
  
  function write($key, $value=null, $options=array())
  {
    $ttl = isset($options['expires_in']) ? $options['expires_in'] : 0;
    $ttl = is_string($ttl) ? strtotime($ttl) : $ttl;
    apc_store($key, $value, $ttl);
  }
  
  function write_once($key, $value=null, $options=array())
  {
    trigger_error("apc_add() always returns true even when it should return false!", E_USER_WARNING);
    $ttl = isset($options['expires_in']) ? $options['expires_in'] : 0;
    $ttl = is_string($ttl) ? strtotime($ttl) : $ttl;
    return apc_add($key, $value, $ttl);
  }
  
  function delete($key) {
    apc_delete($key);
  }
  
  function exists($key) {
    return (apc_fetch($key) === false);
  }
  
  function clear() {
    apc_clear_cache('user');
  }
}

?>
