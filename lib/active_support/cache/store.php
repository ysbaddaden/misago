<?php

# Abstract cache storage.
# 
# See +ActiveSupport_Cache_ApcStore+, +ActiveSupport_Cache_MemcacheStore+
# or +ActiveSupport_Cache_FileStore+ for actual implementations. You may
# build your own implementation, too.
# 
# Note: +ActiveSupport_Cache+ is meant to store strings. An implementation
# may store something else (like objects), but that shouldn't be used.
abstract class ActiveSupport_Cache_Store extends Object
{
  # Gets a variable.
  abstract function read($key);
  
  # Sets a variable.
  # 
  # Possible options:
  # 
  # - `expires_in`: the number of seconds that this value may live in cache.
  # 
  abstract function write($key, $value, $options=array());
  
  # Deletes a variable.
  abstract function delete($key);
  
  # Checks if a variable has been set (and hasn't expired yet).
  abstract function exists($key);
  
  # Invalidates the whole cache at once.
  abstract function clear();
  
  # Increments a variable by `amount`.
  function increment($key, $amount=1)
  {
    $value = $this->fetch($key, 0) + $amount;
    $this->write($key, $value);
    return $value;
  }
  
  # Decrements a variable by `amount`.
  function decrement($key, $amount=1)
  {
    $value = max($this->fetch($key, 0) - $amount, 0);
    $this->write($key, $value);
    return $value;
  }
  
  # Gets a variable if available, otherwise returns `default`.
  function fetch($key, $default=null)
  {
    $value = $this->read($key);
    return ($value === false) ? $default : $value;
  }
}

?>
