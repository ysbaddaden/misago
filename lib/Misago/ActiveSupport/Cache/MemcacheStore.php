<?php
namespace Misago\ActiveSupport\Cache;

# A Cache Store implementation which stores data with Memcached.
# See <tt>Store</tt> for help.
class MemcacheStore extends Store
{
  private $memcache = null;
  
  function __construct()
  {
    $this->memcache = new \Memcache();
  
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
    if (!$this->memcache->replace($key, $value, $expires_in)) {
      $this->memcache->set($key, $value, $expires_in);
    }
  }
  
  function read_multiple($keys)
  {
    return $this->memcache->getMulti($keys);
  }
  
  function write_multiple($keys, $options=array())
  {
    $expires_in = isset($options['expires_in']) ? $options['expires_in'] : 0;
    $this->memcache->setMulti($keys, $expires_in);
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
