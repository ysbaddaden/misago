<?php

# A Cache Store implementation which stores data with Memcached.
# See +ActiveSupport_Cache_Store+ for help.
class ActiveSupport_Cache_MemcacheStore extends ActiveSupport_Cache_Store
{
  protected $flag = null;
  
  function __construct()
  {
    $this->memcache = new Memcache();
  
    $servers = func_num_args() ? func_get_args() : array('localhost:11211');
    foreach($servers as $server)
    {
      if (strpos($server, ':') !== false) {
        list($host, $port) = explode(':', $server, 2);
      }
      else
      {
        $host = $server;
        $port = '11211';
      }
      $this->memcache->addServer($host, $port);
    }
  }
  
  function increment($key, $amount=1)
  {
    $value = $this->memcache->increment($key, $amount);
    if ($value === false)
    {
      $this->write($key, $amount);
      return $amount;
    }
    return $value;
  }
  
  function decrement($key, $amount=1)
  {
    $value = $this->memcache->decrement($key, $amount);
    if ($value === false)
    {
      $this->write($key, 0);
      return 0;
    }
    return $value;
  }
  
  function read($key)
  {
    return $this->memcache->get($key);
  }
  
  function write($key, $value, $options=array())
  {
    $expires_in = isset($options['expires_in']) ? $options['expires_in'] : 0;
    if (!$this->memcache->replace($key, $value, $this->flag, $expires_in)) {
      $this->memcache->set($key, $value, $this->flag, $expires_in);
    }
  }
  
  function delete($key)
  {
    $this->memcache->delete($key);
  }
  
  function exists($key)
  {
    return ($this->read($key) === false);
  }
  
  function clear()
  {
    $this->memcache->flush();
  }
}

?>
