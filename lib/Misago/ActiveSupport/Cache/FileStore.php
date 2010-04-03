<?php
namespace Misago\ActiveSupport\Cache;

# An Cache Store implementation which stores data in files.
# See <tt>Store</tt> for help.
class FileStore extends Store
{
  private $cache_dir;
  
  function __construct()
  {
    $this->cache_dir = TMP.'/cache/file_store/';
    if (!file_exists($this->cache_dir)) {
      mkdir($this->cache_dir, 0775, true);
    }
  }
  
  function read($key)
  {
    if (is_array($key))
    {
      $rs = array();
      foreach($key as $k)
      {
        $v = $this->read($k);
        if ($v !== false) {
          $rs[$k] = $v;
        }
      }
      return $rs;
    }
    return $this->exists($key) ? file_get_contents($this->file($key)) : false;
  }
  
  function write($key, $value=null, $options=array())
  {
    if (is_array($key))
    {
      foreach($key as $k => $v) {
        $this->write($k, $v);
      }
      return;
    }
    $file = $this->file($key);
    file_put_contents($file, $value);
  }
  
  function write_once($key, $value=null, $options=array())
  {
    trigger_error("FileStore isn't compatible with write_once, use MemcacheStore or RedisStore instead.", E_USER_WARNING);
    $this->write($key, $value, $options);
    return true;
  }
  
  function delete($key)
  {
    if (is_array($key))
    {
      foreach($key as $k) {
        $this->delete($k);
      }
      return;
    }
    $file = $this->file($key);
    if (file_exists($file)) {
      unlink($file);
    }
  }
  
  function exists($key)
  {
    $file = $this->file($key);
    return file_exists($file);
  }
  
  function clear()
  {
    $d = dir($this->cache_dir);
    while(($file = $d->read()) !== false)
    {
      if ($file == '.' or $file == '..') continue;
      unlink($this->cache_dir.$file);
    }
    $d->close();
  }
  
  private function file($key)
  {
    return $this->cache_dir.md5($key);
  }
}

?>
