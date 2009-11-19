<?php

# Abstract cache storage.
# 
# See <tt>ActiveSupport_Cache_MemoryStore</tt>, <tt>ActiveSupport_Cache_MemcacheStore</tt>
# or <tt>ActiveSupport_Cache_FileStore</tt> for actual implementations. You may
# build your own implementation, too.
# 
# Note: <tt>ActiveSupport_Cache</tt> is meant to store strings. Some implementations
# may store something else (like objects), but that shouldn't be used.
abstract class ActiveSupport_Cache_Store extends Misago_Object
{
  # Gets a variable.
  abstract function read($key);
  
  # Sets a variable.
  # 
  # - expires_in: the number of seconds that this value may live in cache.
  # 
  abstract function write($key, $value, $options=array());
  
  # Deletes a variable.
  abstract function delete($key);
  
  # Checks if a variable has been set (and hasn't expired yet).
  abstract function exists($key);
  
  # Invalidates the whole cache at once.
  abstract function clear();
  
  # Increments a variable by +$amount+.
  function increment($key, $amount=1)
  {
    $value = $this->fetch($key, 0) + $amount;
    $this->write($key, $value);
    return $value;
  }
  
  # Decrements a variable by +$amount+.
  function decrement($key, $amount=1)
  {
    $value = max($this->fetch($key, 0) - $amount, 0);
    $this->write($key, $value);
    return $value;
  }
  
  # Gets a variable if available, otherwise returns +$default+.
  function fetch($key, $default=null)
  {
    $value = $this->read($key);
    return ($value === false) ? $default : $value;
  }
}

?>
