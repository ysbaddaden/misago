<?php
namespace Misago\ActiveSupport\Cache;
require 'Predis.php';

# A Cache Store implementation which stores data with Redis.
# See <tt>Store</tt> for help.
class RedisStore extends Store
{
  private $redis;
  
  function __construct()
  {
    if (!func_num_args()) {
      $this->redis = new \Predis\Client();
    }
    else
    {
      $servers = func_get_args();
      foreach($servers as $i => $server)
      {
        if (strpos($server, ':') !== false) {
          list($host, $port) = explode(':', $server, 2);
        }
        else
        {
          $host = $server;
          $port = '6379';
        }
        $servers[$i] = array('host' => $host, 'port' => $port);
      }
      $this->redis = forward_static_call(array('\Predis\Client', 'create'), $servers);
    }
  }
  
  function increment($key, $amount=1)
  {
    return $this->redis->incrby($key, $amount);
  }
  
  function decrement($key, $amount=1)
  {
    if ($this->exists($key)) {
      return $this->redis->decrby($key, $amount);
    }
    $this->write($key, 0);
    return 0;
  }
  
  function read($key)
  {
    $value = $this->redis->get($key);
    return ($value === null) ? false : $value;
  }
  
  function write($key, $value, $options=array())
  {
    $this->redis->set($key, $value);
    
    if (isset($options['expires_in'])) {
      $this->redis->expire($key, $options['expires_in']);
    }
  }
  
  function delete($key)
  {
    $this->redis->del($key);
  }
  
  function exists($key)
  {
    return (bool)$this->redis->exists($key);
  }
  
  function clear()
  {
    $this->redis->flushdb();
  }
}

?>
