<?php
namespace Misago\ActiveSupport\Cache;

# A Cache Store implementation which stores data using APC.
# See <tt>Misago\ActiveRecord\Cache\Store</tt> for help.
class MemoryStore extends Store
{
  function read($key) {
    return apc_fetch($key);
  }
  
  function write($key, $value=null, $options=array()) {
    apc_store($key, $value, $this->ttl($options));
  }
  
  function write_once($key, $value=null, $options=array()) {
    return apc_add($key, $value, $this->ttl($options));
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
