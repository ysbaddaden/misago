<?php
namespace Misago\ActiveSupport\Cache;

# Abstract cache storage.
# 
# See <tt>MemoryStore</tt>, <tt>MemcacheStore</tt>
# or <tt>FileStore</tt> for actual implementations. You may
# build your own implementation, too.
# 
# Note: <tt>ActiveSupport\Cache</tt> is meant to store strings. Some implementations
# may store something else (like objects), but that shouldn't be used.
abstract class Store extends \Misago\Object
{
  # Gets a variable.
  abstract function read($key);
  
  # Sets a variable.
  # 
  # - expires_in: the number of seconds that this value may live in cache.
  # 
  abstract function write($key, $value, $options=array());
  
  # Gets multiple variables at once.
  function read_multiple($keys)
  {
    $rs = array();
    foreach($keys as $key) {
      $rs[$key] = $this->read($key);
    }
    return $rs;
  }
  
  # Sets multiple variables at once.
  function write_multiple($keys, $options=array())
  {
    foreach($keys as $key => $value) {
      $rs[$key] = $this->write($key, $value, $options);
    }
  }
  
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
