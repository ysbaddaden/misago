<?php
namespace Misago\ActiveSupport\Cache;
#require 'Redis.php';

# A Cache Store implementation which uses Redis.
# See <tt>Misago\ActiveSupport\Cache\Store</tt> for actual help.
# 
# Note: RedisStore can't connect to multiple Redis servers.
class RedisStore extends Store
{
  private $redis;
  
  function __construct($config=array())
  {
    if (func_num_args() > 1)
    {
      trigger_error("RedisStore cannot connect to multiple Redis servers.",
        E_USER_WARNING);
    }
    $this->redis = new \Redis\Client($config);
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
  
  private function _write($key, $value=null, $options=array(), $nx=false)
  {
    $ttl = $this->ttl($options);
    
    if (is_array($key))
    {
      $method = $nx ? 'msetnx' : 'mset';
      $rs = $this->redis->$method($key);
      
      if ($ttl !== null)
      {
        $this->redis->pipeline(function($pipe) use($keys)
        {
          foreach(array_keys($keys) as $key) {
            $pipe->expire($key, $ttl);
          }
        });
      }
    }
    else
    {
      $method = $nx ? 'setnx' : 'set';
      $rs = $this->redis->$method($key, $value);
      
      if ($ttl !== null) {
        $this->redis->expire($key, $ttl);
      }
    }
    return $rs;
  }
  
  function write($key, $value=null, $options=array()) {
    $this->_write($key, $value, $options, false);
  }
  
  function write_once($key, $value=null, $options=array()) {
    return $this->_write($key, $value, $options, true);
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
