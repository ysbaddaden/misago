<?php

# A Cache Store implementation which stores data in memory.
# See <tt>ActiveSupport_Cache_Store</tt> for help.
# 
# Beware: variables aren't retained between requests with +MemoryStore+!
class ActiveSupport_Cache_MemoryStore extends ActiveSupport_Cache_Store
{
  private $cache      = array();
  private $expires_at = array();
  
  function read($key)
  {
    return $this->exists($key) ? $this->cache[$key] : false;
  }
  
  function write($key, $value, $options=array())
  {
    $this->cache[$key] = $value;
    $this->expires_at[$key] = isset($options['expires_in']) ? time() + $options['expires_in'] : 0;
  }
  
  function delete($key)
  {
    unset($this->cache[$key]);
  }
  
  function exists($key)
  {
    if (isset($this->cache[$key]))
    {
      if ($this->expires_at[$key] == 0
        or $this->expires_at[$key] > time())
      {
        return true;
      }
      $this->delete($key);
    }
    return false;
  }
  
  function clear()
  {
    $this->cache      = array();
    $this->expires_at = array();
  }
}

?>
