<?php
namespace Misago\ActionController\Caching;
use Misago\ActiveSupport;

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
# means that +/members.rss+ and +/members.rss?limit=10+ will share the
# same cache file.
# 
# ==Example
# 
#   class PostsController extends Misago\ActionController\Base
#   {
#     protected $caches_page = array(
#       'index',
#       'feed' => array('unless' => array(':format' => 'html'))
#     );
#     
#     function create()
#     {
#       $this->expire_page(array(':action' => 'index'));
#     }
#   }
# 
# ==Conditions
# 
# You may set conditions with +if+ and +unless+ options. Both
# accept any parameter that might be present in path parameters.
# 
# 
# =Action caching
# 
# In action caching, the request goes throught the Action Controller,
# but the action will not be processed if a cache is available.
# Filters will be processed thought, which allows to cache pages
# only available to authenticated users for instance.
# 
# The cache key is made from the host (and port), the path of the
# request and the GET parameters. Which means that +x.domain.com/list+,
# +y.domain.com/list+ and +x.domain.com/list?page=2+ will be different
# caches. This allows for subdomains personalization for instance.
# 
# ==Example
# 
#   class PostsController extends Misago\ActionController\Base
#   {
#     protected $before_filters = array(
#       'authenticate' => array('only' => 'feed')
#     );
#     protected $caches_action = array(
#       'index',
#       'feed' => array('if' => array(':format' => 'html'))
#     );
#   }
# 
# TODO: :layout => false to only cache the view (while still rendering the layout).
# TODO: :cache_path
# TODO: :expires_in
# 
# 
# =Fragment caching [todo]
# 
# 
# =HTTP browser cache
# 
# 
abstract class Caching extends Filters
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
  
	# See <tt>Misago\ActiveSupport\Cache</tt>.
  function cache()
  {
    if (!isset($this->cache))
    {
      $cache_store = cfg_get('cache_store', 'memory_store');
      $CacheStoreClassName = 'ActiveSupport\Cache\\'.String::camelize($cache_store);
      $this->cache = new $CacheStoreClassName();
    }
    return $this->cache;
  }
  
  
  # Manually caches the current request as a real file into the +public+ folder.
  function cache_page($content=null, $options=null)
  {
    $path = $this->page_cache_key($options);
    $dir  = dirname($path);
    if (!empty($dir) and !file_exists(ROOT.'/public'.$dir)) {
      mkdir(ROOT.'/public'.$dir, 0775, true);
    }
    $this->logger->log_debug() && $this->logger->debug("Caching page in public/$path");
    file_put_contents(ROOT.'/public'.$path, ($content === null) ? $this->response->body : $content);
  }
  
  # Deletes a cached page.
  function expire_page($options=null)
  {
    $path = $this->page_cache_key($options);
    if (file_exists(ROOT.'/public'.$path))
    {
      $this->logger->log_debug() && $this->logger->debug("Expired page public/$path");
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
    $key = $this->fragment_cache_key($options);
    $this->logger->log_debug() && $this->logger->debug("Expired fragment $key");
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
  function expires_in($seconds, $options=array())
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
  function expires_now()
  {
    $this->response->headers['Cache-Control'] = 'no-cache, no-store';
  }
  
  
  # :private:
  protected function shall_we_cache_page()
  {
    if (isset($this->caches_page[$this->action]))
    {
      $cache = true;
      
      if (isset($this->caches_page[$this->action]['unless']))
      {
        $test = array_intersect_assoc($this->params, $this->caches_page[$this->action]['unless']);
        $cache &= empty($test);
      }
      if (isset($this->caches_page[$this->action]['if']))
      {
        $test = array_intersect_assoc($this->params, $this->caches_page[$this->action]['if']);
        $cache &= (!empty($test));
      }
      
      return $cache;
    }
    return false;
  }
  
  # :private:
  protected function shall_we_cache_action()
  {
    if (isset($this->caches_page[$this->action]))
    {
      $cache = true;
      
      if (isset($this->caches_action[$this->action]['unless']))
      {
        $test  = array_intersect_assoc($this->params, $this->caches_action[$this->action]['unless']);
        $cache &= empty($test);
      }
      if (isset($this->caches_action[$this->action]['if']))
      {
        $test  = array_intersect_assoc($this->params, $this->caches_action[$this->action]['if']);
        $cache &= (!empty($test));
      }
      
      return $cache;
    }
    return false;
  }
  
  # :private:
  protected function cache_action()
  {
    $options = array_merge(array('path_only' => false, ':format' => $this->format), $this->params);
    $key     = $this->fragment_cache_key($options);
    $content = $this->cache->read($key);
    
    $this->logger->log_debug() && $this->logger->debug("Caching action $key");
    
    if ($content === false)
    {
      $this->process_action();
      $this->cache->write($key, $this->response->body);
    }
    else {
      $this->response->body = $content;
    }
  }
  
  private function page_cache_key($options)
  {
    switch(gettype($options))
    {
      case 'array':
        $options = array_merge(array(
          ':controller' => $this->params[':controller'],
          ':action'     => $this->action,
          ':format'     => $this->format,
          ':path_only'  => true,
        ), $options);
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
  
  function fragment_cache_key($options)
  {
    switch(gettype($options))
    {
      case 'array':
        $options = array_merge(array(
          ':controller' => $this->params[':controller'],
          ':format'     => $this->format,
          ':path_only'  => true,
        ), $options);
        $suffix = isset($options[':action_suffix']) ? $options[':action_suffix'] : '';
        return url_for($options).$suffix;
      break;
      
      default: return $options;
    }
  }
}

?>
