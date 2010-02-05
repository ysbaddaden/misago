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
    if (is_array($key))
    {
      $rs = $this->redis->pipeline(function($pipe) use($key, $amount)
      {
        foreach($key as $k) {
          $pipe->incrby($k, $amount);
        }
      });
      return $this->build_response($key, $rs);
    }
    return $this->redis->incrby($key, $amount);
  }
  
  function decrement($key, $amount=1)
  {
    if (is_array($key))
    {
      $rs = $this->redis->pipeline(function($pipe) use($key, $amount)
      {
        foreach($key as $k) {
          $pipe->decrby($k, $amount);
        }
      });
      return $this->build_response($key, $rs);
    }
    if ($this->exists($key)) {
      return $this->redis->decrby($key, $amount);
    }
    $this->write($key, 0);
    return 0;
  }
  
  function read($key)
  {
    if (is_array($key))
    {
      $rs = $this->redis->mget($key);
      return $this->build_response($key, $rs);
    }
    
    $value  = $this->redis->get($key);
    return ($value === null) ? false : $value;
  }
  
  function write($key, $value=null, $options=array())
  {
    if (is_array($key))
    {
      $this->redis->mset($key);
      
      if (isset($options['expires_in']))
      {
        $this->redis->pipeline(function($pipe)
        {
          foreach(array_keys($keys) as $key) {
            $pipe->expire($key, $expires_in);
          }
        });
      }
    }
    else
    {
      $this->redis->set($key, $value);
      
      if (isset($options['expires_in'])) {
        $this->redis->expire($key, $options['expires_in']);
      }
    }
  }
  
  function delete($key) {
    $this->redis->del($key);
  }
  
  function exists($key) {
    return (bool)$this->redis->exists($key);
  }
  
  function clear() {
    $this->redis->flushdb();
  }
  
  private function & build_response($keys, $rs)
  {
    $values = array();
    foreach($rs as $i => $v)
    {
      if ($v !== null) {
        $values[$keys[$i]] = $v;
      }
    }
    return $values;
  }
}

?>
