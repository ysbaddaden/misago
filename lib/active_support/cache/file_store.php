<?php

# An Cache Store implementation which stores data in files.
# See <tt>ActiveSupport_Cache_Store</tt> for help.
class ActiveSupport_Cache_FileStore extends ActiveSupport_Cache_Store
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
    return $this->exists($key) ? file_get_contents($this->file($key)) : false;
  }
  
  function write($key, $value, $options=array())
  {
    $file = $this->file($key);
    file_put_contents($file, $value);
    file_put_contents("$file.expires", isset($options['expires_in']) ?
      time() + $options['expires_in'] : 0);
  }
  
  function delete($key)
  {
    $file = $this->file($key);
    if (file_exists($file))
    {
      unlink($file);
      unlink("$file.expires");
    }
  }
  
  function exists($key)
  {
    $file = $this->file($key);
    if (file_exists($file))
    {
      $expires_at = file_get_contents("$file.expires");
      if ($expires_at == 0 or $expires_at > time()) {
        return true;
      }
      $this->delete($key);
    }
    return false;
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
