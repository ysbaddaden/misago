<?php

# A Cache Store implementation which stores data with APC.
# See <tt>ActiveSupport_Cache_Store</tt> for help.
class ActiveSupport_Cache_MemoryStore extends ActiveSupport_Cache_Store
{
  function read($key)
  {
    return apc_fetch($key);
  }
  
  function write($key, $value, $options=array())
  {
    $ttl = isset($options['expires_in']) ? $options['expires_in'] : 0;
    apc_store($key, $value, $ttl);
  }
  
  function delete($key)
  {
    apc_delete($key);
  }
  
  function exists($key)
  {
    return (apc_fetch($key) === false);
  }
  
  function clear()
  {
    apc_clear_cache('user');
  }
}

?>
