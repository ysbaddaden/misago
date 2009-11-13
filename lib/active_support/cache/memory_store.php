<?php

# A Cache Store implementation which stores data in memory.
# See <tt>ActiveSupport_Cache_Store</tt> for help.
# 
# Beware: variables aren't retained between requests with +MemoryStore+!
class ActiveSupport_Cache_MemoryStore extends ActiveSupport_Cache_Store
{
  private $cache = array();
  
  function read($key)
  {
    return $this->exists($key) ? $this->cache[$key] : false;
  }
  
  function write($key, $value, $options=array())
  {
    $this->cache[$key] = $value;
  }
  
  function delete($key)
  {
    unset($this->cache[$key]);
  }
  
  function exists($key)
  {
    return isset($this->cache[$key]);
  }
  
  function clear()
  {
    $this->cache = array();
  }
}

?>
