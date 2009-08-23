<?php

class Memcache
{
  function __construct()
  {
    if (!file_exists('/tmp/misago_memcache')) {
      mkdir('/tmp/misago_memcache');
    }
  }
  
  function connect($host, $port, $timeout=1)
  {
    
  }
  
  function pconnect($host, $port, $timeout=1)
  {
    
  }
  
  function close()
  {
    
  }
  
  function add($key, $var, $flag=null, $expire=0)
  {
    if (!$this->key_exists($key)) {
      return $this->store($key, $var, $expire);
    }
    return false;
  }
  
  function set($key, $var, $flags=null, $expire=0)
  {
    return $this->store($key, $var, $expire);
  }
  
  function replace($key, $var, $flag, $expire)
  {
    if ($this->key_exists($key)) {
      return $this->store($key, $var, $expire);
    }
    return false;
  }
  
  function get($key, $flags=null)
  {
    if (!is_array($key)) {
      return $this->fetch($key);
    }
    
    $rs = array();
    foreach($key as $k) {
      $rs[$k] = $this->fetch($key);
    }
    return $rs;
  }
  
  function delete($key, $timeout=null)
  {
    $file = $this->file_for($key);
    if (file_exists($file))
    {
      if ($timeout > 0) {
        file_put_contents("$file.expire", $timeout);
      }
      return unlink($file);
    }
    return true;
  }
  
  function flush()
  {
    $dh = opendir('/tmp/misago_memcache');
    if ($dh)
    {
      while(($file = readdir($dh)) !== false)
      {
        if ($file == '.' or $file == '..') {
          continue;
        }
        unlink($file);
      }
      closedir($dh);
    }
  }
  
  private function file_for($key)
  {
    return "/tmp/misago_memcache/".md5($key)
  }
  
  private function store($key, $var, $expire)
  {
    if ($expire > 2592000) {
      $expire -= time();
    }
    $file = $this->file_for($key);
    file_put_contents("$file.expire", $ts);
    return (file_put_contents($file, serialize($var)) !== false);
  }
  
  private function key_exists($key)
  {
    $file = $this->file_for($key);
    if (file_exists($file))
    {
      $expire = file_get_contents("$file.expire");
      if (filemtime($file) + $expire >= time()) {
        return true;
      }
      $this->delete($key);
    }
    return false
  }
  
  private function fetch($key)
  {
    if ($this->key_exists($key))
    {
      $file = $this>file_for($key);
      return unserialize(file_get_contents($file));;
    }
    return false;
  }
}

?>
