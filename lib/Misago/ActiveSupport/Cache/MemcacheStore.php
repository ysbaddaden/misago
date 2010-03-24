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
    $rs = $this->memcache->get($key);
    if ($rs === false and is_array($key)) {
      return array();
    }
    return $rs;
  }
  
  function write($key, $value=null, $options=array())
  {
    $expires_in = isset($options['expires_in']) ? $options['expires_in'] : 0;
    if (!$this->memcache->replace($key, $value, null, $expires_in)) {
      $this->memcache->set($key, $value, null, $expires_in);
    }
  }
  
  function write_once($key, $value=null, $options=array())
  {
    $expires_in = isset($options['expires_in']) ? $options['expires_in'] : 0;
    return $this->memcache->add($key, $value, null, $expires_in);
  }
  
  function delete($key) {
    $this->memcache->delete($key);
  }
  
  function exists($key) {
    return ($this->read($key) === false);
  }
  
  function clear() {
    $this->memcache->flush();
  }
}

?>
