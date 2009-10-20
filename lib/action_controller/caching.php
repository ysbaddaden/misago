<?php

abstract class ActionController_Caching extends Object
{
	# See +ActiveSupport_Cache+.
	public $cache;
  
  function __construct()
  {
    $CacheStoreClassName = 'ActiveSupport_Cache_'.String::camelize(cfg::is_set('cache_store') ? cfg::get('cache_store') : 'apc_store');
    $this->cache = new $CacheStoreClassName();
  }
  
  # Sends a Cache-Control header for HTTP caching.
  # 
  # Defaults to private, telling proxies not to cache anything,
  # which allows for some privacy of content.
  # 
  # Examples:
  # 
  #   expires_in(3600)
  #   expires_in('+1 hour', array('private' => false))
  # 
  protected function expires_in($seconds, $options=array())
  {
    $cache_control = array(
      'max-age' => is_integer($seconds) ? $seconds : strtotime($seconds),
      'private' => true,
    );
    foreach($options as $k => $v)
    {
      if (!$v) {
        continue;
      }
      $cache_control[] = ($v === true) ? $k : "$k=$v";
    }
    $this->response->headers['Cache-Control'] = implode(', ', $cache_control);
  }
  
  # Sends a Cache-Control header with 'no-cache' to disallow or
  # cancel HTTP caching of current request.
  protected function expires_now()
  {
    $this->response->headers['Cache-Control'] = 'no-cache, no-store';
  }
}

?>
