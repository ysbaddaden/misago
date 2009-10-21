<?php

# Caching.
# 
# =Page caching
# 
# Caches the current action as a real file in the public directory,
# that matches the path of the current request. That way the web
# server will serve this generated page directly, without having to
# reach the framework.
# 
# Of course authentification and other filters cannot occur for these
# cached pages, since the application isn't even reached. If you need
# authentification check action caching below.
# 
# Please note that GET parameters are overlooked by the cache, which
# means that `/members.rss` and `/members.rss?limit=10` will share the
# same cache file.
# 
# ==Example
# 
#   class PostsController extends ActionController_Base
#   {
#     protected $caches_page = array(
#       'index',
#       'feed' => array('unless' => array(':format' => 'html'))
#     );
#   }
# 
# ==Conditions
# 
# You may set conditions with `if` and `unless` options. Both will
# accept any parameter that might be present in `$this->format`.
# 
# 
# =Action caching [todo]
# 
# Sometimes you need a user be authentified before serving pages, and
# thus can't use page caching. This is were action caching comes in.
# 
# In action caching, the framework will be  started, the controller
# created and the filters executed, but the action will not be processed
# if a cache is available.
# 
# ==Example
# 
#   class PostsController extends ActionController_Base
#   {
#     protected $caches_action = array(
#       'index',
#       'feed' => array('if' => array(':format' => 'html'))
#     );
#   }
# 
# 
# =Fragment caching [todo]
# 
# 
# =HTTP browser cache
# 
# 
abstract class ActionController_Caching extends Object
{
  protected $caches_page   = array();
  protected $caches_action = array();
  
  function __construct()
  {
    foreach($this->caches_page as $k => $v)
    {
      if (is_integer($k))
      {
        $this->caches_page[$v] = array();
        unset($this->caches_page[$k]);
      }
    }
    
    foreach($this->caches_action as $k => $v)
    {
      if (is_integer($k))
      {
        $this->caches_action[$v] = array();
        unset($this->caches_action[$k]);
      }
    }
  }
  
	# See +ActiveSupport_Cache+.
  function cache()
  {
    if (!isset($this->cache))
    {
      $cache_store = cfg::is_set('cache_store') ? cfg::get('cache_store') : 'apc_store';
      $CacheStoreClassName = 'ActiveSupport_Cache_'.String::camelize($cache_store);
      $this->cache = new $CacheStoreClassName();
    }
    return $this->cache;
  }
  
  # Manually caches the current request as a real file into the `public` folder.
  function cache_page($content=null, $options=null)
  {
    $path = $this->cache_page_key($options);
    $dir  = dirname($path);
    if (!empty($dir) and !file_exists(ROOT.'/public'.$dir)) {
      mkdir(ROOT.'/public'.$dir, 0775, true);
    }
    file_put_contents(ROOT.'/public'.$path, ($content === null) ? $this->response->body : $content);
  }
  
  # Deletes cached page.
  function expire_page($options=null)
  {
    $path = $this->cache_page_key($options);
    if (file_exists(ROOT.'/public'.$path)) {
      unlink(ROOT.'/public'.$path);
    }
  }
  
  # Deletes cached action.
  function expire_action($options)
  {
    $this->expire_fragment($options);
  }
  
  # Deletes a cached fragment.
  function expire_fragment($options)
  {
    $key = $this->cache_fragment_key($options);
    $this->cache->delete($key);
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
      'max-age' => is_integer($seconds) ? (time() + $seconds) : strtotime($seconds),
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
  
  # @private
  protected function _cache_page()
  {
    $cache = true;
    
    if (isset($this->caches_page[$this->action]['unless']))
    {
      $test  = array_intersect_assoc($this->params, $this->caches_page[$this->action]['unless']);
      $cache = empty($test);
    }
    if (isset($this->caches_page[$this->action]['if']))
    {
      $test  = array_intersect_assoc($this->params, $this->caches_page[$this->action]['if']);
      $cache = (!empty($test));
    }
    if ($cache) {
      $this->cache_page();
    }
  }
  
  # @private
  protected function matches_cache_action()
  {
    $cache = false;
    
    if (isset($this->caches_action[$this->action]['unless']))
    {
      $test  = array_intersect_assoc($this->params, $this->caches_action[$this->action]['unless']);
      $cache = empty($test);
    }
    if (isset($this->caches_action[$this->action]['if']))
    {
      $test  = array_intersect_assoc($this->params, $this->caches_action[$this->action]['if']);
      $cache = (!empty($test));
    }
    return $cache;
  }
  
  # @private
  protected function _cache_action()
  {
    $key     = $this->request->host().$this->request->port_string().$this->request->path();
    $content = $this->cache->read($key);
    
    if ($content === false)
    {
      $this->_process_action();
      $this->cache->write($key, $this->response->body);
    }
    else {
      echo $content;
    }
  }
  
  private function cache_page_key($options)
  {
    switch(gettype($options))
    {
      case 'array':
        if (!isset($options[':controller'])) {
          $options[':controller'] = $this->params[':controller'];
        }
        $path = url_for($options);
      break;
      case 'string': $path = $options; break;
      default:       $path = $this->request->path(); break;
    }
    if ($path == '' or $path == '/') {
      $path = "/index.{$this->format}";
    }
    return $path;
  }
  /*
  private function cache_fragment_key($options)
  {
    if (!isset($options[':controller'])) {
      $options[':controller'] = $this->params[':controller'];
    }
    if (!isset($options[':format'])) {
      $options[':format'] = $this->format;
    }
    $suffix = isset($options[':action_suffix']) ? $options[':action_suffix'] : '';
    return url_for($options).$suffix;
  }
  */
}

?>
