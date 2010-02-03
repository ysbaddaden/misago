<?php
namespace Misago\ActiveSupport\Cache;

# Abstract cache storage.
# 
# See <tt>MemoryStore</tt>, <tt>MemcacheStore</tt>, <tt>RedisStore</tt> or
# <tt>FileStore</tt> for actual implementations. You may build your own
# one too.
# 
# Note: <tt>ActiveSupport\Cache</tt> is meant to store strings. Some
# implementations may store something else (like objects), but that shouldn't
# be used.
# 
abstract class Store extends \Misago\Object
{
  private static $singletons = array();
  
  # Gets a variable.
  # 
  #   $user_id = $store->read('user_id');
  #   list($ûser_id, $user_name) = $store->read(array('user_id', 'user_name'));
  # 
  abstract function read($key);
  
  # Sets a variable.
  # 
  #   $store->write('user_id', 123);
  #   $store->write(array('user_id' => 123, 'name' => 'John Doe'));
  # 
  # - expires_in: the number of seconds that this value may live in cache.
  # 
  abstract function write($key, $value=null, $options=array());
  
  # Deletes a variable.
  # 
  #   $store->delete('user_id');
  #   $store->delete(array('user_id', 'user_name'));
  # 
  abstract function delete($key);
  
  # Checks if a variable has been set (and hasn't expired yet).
  abstract function exists($key);
  
  # Invalidates the whole cache at once.
  abstract function clear();
  
  # Increments a variable by +$amount+.
  function increment($key, $amount=1)
  {
    if (is_array($key))
    {
      $rs = array();
      foreach($key as $k) {
        $rs[$k] = $this->increment($k, $amount);
      }
      return $rs;
    }
    $value = $this->fetch($key, 0) + $amount;
    $this->write($key, $value);
    return $value;
  }
  
  # Decrements a variable by +$amount+.
  function decrement($key, $amount=1)
  {
    if (is_array($key))
    {
      $rs = array();
      foreach($key as $k) {
        $rs[$k] = $this->decrement($k, $amount);
      }
      return $rs;
    }
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
  
  static function singleton()
  {
    if (!isset(self::$singletons[get_called_class()])) {
      return self::$singletons[get_called_class()] = new static();
    }
    return self::$singletons[get_called_class()];
  }
}

?>
