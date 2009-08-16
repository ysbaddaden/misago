<?php

class ActionController_Routing extends Object
{
  private $routes          = array();
  private $default_mapping = array(
    ':method'      => 'GET',
    ':controller'  => 'index',
    ':action'      => 'index',
    ':format'      => null,
    'conditions'   => array('method' => 'ANY'),
    'requirements' => array(),
  );
  private static $map;
  private static $current_format = null;
  
  # Singleton
  static function draw()
  {
    if (self::$map === null) {
      self::$map = new self();
    }
    return self::$map;
  }
  
  # Empties the routes.
  function reset()
  {
    $this->routes = array();
  }
  
  # Connects a path to a mapping.
  function connect($path, $mapping=array())
  {
    $this->connect_route(null, $path, $mapping);
  }
  
  # Connects the homepage
  function root(array $mapping)
  {
    foreach($this->routes as $i => $route)
    {
      if ($route['path'] == '') {
        unset($this->routes[$i]);
      }
    }
    $this->named('root', '', $mapping);
  }
  
  # Connects a path to a mapping, giving the route a name.
  function named($name, $path, $mapping=array())
  {
    return $this->connect_route($name, $path, $mapping);
  }
  
  private function connect_route($name, $path, $mapping)
  {
    $regexp = $path;
    
    if (!empty($mapping['requirements']))
    {
      foreach($mapping['requirements'] as $k => $re)
      {
        $param  = str_replace(':', '', $k);
        $regexp = str_replace($k, "(?<$param>($re))", $regexp);
      }
    }
    
    $rules = array(
      '#(?<![\/\.\?]):([\w_-]+)#u' => '(?<$1>[^\/\.\?]*)?',          # :param
      '#([\/\.\?]):([\w_-]+)#u'    => '(?:\\\$1(?<$2>[^\/\.\?]*))?', # [/.?]:param
      '#([\/\.\?])\*([\w_-]+)#u'   => '(?:\\\$1(?<$2>.*?))?',        # [/.?]*path
    );
    $regexp = preg_replace(array_keys($rules), array_values($rules), $regexp);
    
    $keys = array();
    foreach(preg_split('/[\.\/\?]/', $path, -1, PREG_SPLIT_NO_EMPTY) as $key)
    {
      if ($key[0] == ':'
        and $key != ':controller'
        and $key != ':action'
        and $key != ':format')
      {
        $keys[] = $key;
      }
    }
    
    $this->routes[] = array(
      'path'    => $path,
      'regexp'  => "#^$regexp$#u",
      'mapping' => &$mapping,
      'keys'    => &$keys,
      'default' => empty($mapping),
      'name'    => $name,
    );
  }
  
  # Builds RESTful connections.
  function resource($name)
  {
    $plural   = $name;
    $singular = String::singularize($name);
    
    $this->named("$plural",          "$name.:format",          array(':controller' => $name, ':action' => 'index',  'conditions' => array('method' => 'GET')));
    $this->named("new_$singular",    "$name/new.:format",      array(':controller' => $name, ':action' => 'neo',    'conditions' => array('method' => 'GET')));
    $this->named("show_$singular",   "$name/:id.:format",      array(':controller' => $name, ':action' => 'show',   'conditions' => array('method' => 'GET')));
    $this->named("edit_$singular",   "$name/:id/edit.:format", array(':controller' => $name, ':action' => 'edit',   'conditions' => array('method' => 'GET')));
    $this->named("create_$singular", "$name.:format",          array(':controller' => $name, ':action' => 'create', 'conditions' => array('method' => 'POST')));
    $this->named("update_$singular", "$name/:id.:format",      array(':controller' => $name, ':action' => 'update', 'conditions' => array('method' => 'PUT')));
    $this->named("delete_$singular", "$name/:id.:format",      array(':controller' => $name, ':action' => 'delete', 'conditions' => array('method' => 'DELETE')));
  }
  
  
  # Returns a mapping for a given method+path.
  function & route($method, $uri)
  {
    $uri = trim($uri, '/');
    
    # searches for a route
    foreach($this->routes as $route)
    {
      $condition_method = isset($route['mapping']['conditions']['method']) ?
        $route['mapping']['conditions']['method'] : $this->default_mapping['conditions']['method'];
      
      if ($condition_method != 'ANY'
        and $condition_method != $method)
      {
        continue;
      }
      
      if (preg_match($route['regexp'], $uri, $matches))
      {
        $mapping = array_merge($this->default_mapping, $route['mapping']);
        foreach($matches as $k => $v)
        {
          if (!is_integer($k) and !empty($v)) {
            $mapping[":$k"] = $v;
          }
        }
        
        break;
      }
    }
    
    if (!isset($mapping)) {
      throw new MisagoException("No route for '$method /$uri'", 404);
    }
    
    self::$current_format = $mapping[':format'];
    
    unset($mapping['conditions']);
    unset($mapping['requirements']);
    
    $mapping[':method'] = $method;
    return $mapping;
  }
  
  # Returns a path for a given mapping.
  # 
  # FIXME: Handle special requirements for keys to select the route.
  function reverse(array $mapping)
  {
    $_mapping = array_merge(array(
      ':controller' => '',
      ':action'     => 'index',
    ), $mapping);
    
    foreach($this->routes as $route)
    {
      # has controller?
      if ((isset($route['mapping'][':controller']) and $_mapping[':controller'] == $route['mapping'][':controller'])
        or strpos($route['path'], ':controller') !== false)
      {
        # has action?
        if ((isset($route['mapping'][':action']) and $_mapping[':action'] == $route['mapping'][':action'])
          or strpos($route['path'], ':action') !== false)
        {
          # has keys?
          if ($this->has_keys($route, $_mapping))
          {
            $path   = strtr($route['path'], $_mapping);
            $path   = str_replace(array('/:format', '.:format', '?:format'), '', $path);
            $method = isset($route['mapping']['conditions']['method']) ? $route['mapping']['conditions']['method'] : 'GET';
            return new ActionController_Path($method, $path);
          }
          
          # default
          $path = ($_mapping[':action'] == 'index') ?
            "{$_mapping[':controller']}" :
            "{$_mapping[':controller']}/{$_mapping[':action']}";
          
          if (isset($_mapping[':format'])) {
            $path .= ".{$_mapping[':format']}";
          }
          $method = isset($route['mapping']['conditions']['method']) ? $route['mapping']['conditions']['method'] : 'GET';
          return new ActionController_Path($method, $path);
        }
      }
    }
    throw new MisagoException("No route for: ".print_r($mapping, true), 500);
  }
  
  private function has_keys($route, $mapping)
  {
    foreach($route['keys'] as $key)
    {
      if (!isset($mapping[$key]))
      {
        return false;
        break;
      }
    }
    return true;
  }
  
  /**
   * Creates helper functions to build paths and URL from routing definition
   * (aka reverse routing). For instance the following route will produce the
   * 'show_product_path()' and 'show_product_url()' functions:
   *
   *   'product/:id' => {:controller => 'products', :action => 'show'}
   * 
   * IMPROVE: Recognize keys' special requirements (?)
   * TODO: Handle :format if defined in route path or in mapping.
   */
  function build_path_and_url_helpers()
  {
    if (DEBUG
      or !file_exists(TMP.'/built_path_and_url_helpers.php')
      or time() - strtotime('-24 hours') > filemtime(TMP.'/built_path_and_url_helpers.php'))
    {
      $functions = array();
      foreach($this->routes as $route)
      {
        if (isset($route['name']))
        {
          $functions[] = $this->build_named_function('path', $route);
          $functions[] = $this->build_named_function('url',  $route);
        }
      }
      $contents = '<?php '.implode("\n\n", $functions).' ?>';
      file_put_contents(TMP.'/built_path_and_url_helpers.php', $contents);
    }
    include TMP.'/built_path_and_url_helpers.php';
  }
  
  private function build_named_function($type, &$route)
  {
    $exported_route = var_export($route, true);
    $func = "function {$route['name']}_{$type}(\$keys=array())
    {
      \$route = $exported_route;
      return ActionController_Routing::named_function_{$type}(\$route, \$keys);
    }";
    return $func;
  }
  
  # @private
  static function named_function_path(&$route, $keys)
  {
    $method = isset($route['mapping']['conditions']['method']) ?
      $route['mapping']['conditions']['method'] : 'GET';
    $path = self::named_function($route, $keys);
    return new ActionController_Path($method, $path);
  }
  
  # @private
  static function named_function_url(&$route, $keys)
  {
    $method = isset($route['mapping']['conditions']['method']) ?
      $route['mapping']['conditions']['method'] : 'GET';
    $path = self::named_function($route, $keys);
    return new ActionController_Url($method, $path);
  }
  
  static private function named_function(&$route, $keys)
  {
    if (is_object($keys) and $keys instanceof ActiveRecord_Record)
    {
      $attributes = $keys->attributes();
      $keys = array();
      foreach($attributes as $k => $v) {
        $keys[":$k"] = $v;
      }
    }
    elseif (!is_array($keys)) {
      $keys = array(':id' => $keys);
    }
    else
    {
      $query = array();
      foreach($keys as $k => $v)
      {
        if (!is_symbol($k))
        {
          $query[$k] = $v;
          unset($keys[$v]);
        }
      }
      $query_string = http_build_query($query);
    }
    
    if (!isset($keys[':format']) and isset(self::$current_format)) {
      $keys[':format'] = self::$current_format;
    }
    
    foreach($route as $k => $v)
    {
      if (is_symbol($k)) {
        $keys[$k] = $v;
      }
    }
    
    $path = strtr($route['path'], $keys);
    $path = ($route['default']) ?
      preg_replace('/[\\/\\.\\?]:[^\\/\\.\\?]+/', '', $path) :
      preg_replace('/[\\/\\.\\?]:format/', '', $path);
    
    return $path.(empty($query_string) ? '' : "?{$query_string}");
  }
}

# Resolves an URL (reverse routing).
# 
# Options:
# 
# - anchor: adds an anchor the the URL.
# - path_only: false to return an absolute URI, true to return the path only (defaults to true).
# - protocol: overwrites the current protocol.
# - host: overwrites the current host.
# - port: overwrites the current port.
# - user: username for HTTP login.
# - password: password for HTTP login.
# 
# Example:
# 
#   url_for(array(':controller' => 'products', ':action' => 'show', ':id' => '67'))
#   # => http://www.domain.com/products/show/67
# 
# Any unknown option that isn't a symbol is added to the query string:
# 
#   url_for(array(':controller' => 'products', 'order' => 'asc'))
#   # => http://www.domain.com/products?order=asc
# 
#   url_for(array(':controller' => 'products', ':action' => 'show', ':id' => 13, 'comments' => 'show'))
#   # => http://www.domain.com/products/show/13?comments=show
# 
# You may also add an anchor:
# 
#   url_for(array(':controller' => 'about', 'anchor' => 'me'))
#   # => http://www.domain.com/about#me
# 
# Using REST resources, you may pass an ActiveRecord directly. For instance:
# 
#   $product = new Product(43);
#   $url = url_for(product);    # => http://www.domain.com/products/3
# 
# IMPROVE: url_for: handle specified options (host, protocol, etc.)
# IMPROVE: url_for: permit for simplified calls, like url_for(array(':action' => 'index')), which shall use the current controller.
function url_for($options)
{
  if ($options instanceof ActiveRecord_Record)
  {
    $named_route = 'show_'.String::underscore(get_class($options)).'_url';
    return $named_route($options);
  }
  
  $default_options = array(
    'anchor'    => null,
    'path_only' => true,
    'protocol'  => cfg::get('current_protocol'),
    'host'      => cfg::get('current_host'),
    'port'      => cfg::get('current_port'),
    'user'      => null,
    'password'  => null,
  );
  $mapping = array_diff_key($options, $default_options);
  $options = array_merge($default_options, $options);
  
  $map  = ActionController_Routing::draw();
  $path = $map->reverse($mapping);
  
  $query_string = array();
  foreach($mapping as $k => $v)
  {
    if (strpos($k, ':') !== 0) {
      $query_string[] = "$k=".urlencode($v);
    }
  }
  
  $path .= (empty($query_string) ? '' : '?'.implode('&', $query_string)).
    (empty($options['anchor']) ? '' : "#{$options['anchor']}");
  
  if ($options['path_only'])
  {
    return $path;
  }
  return cfg::get('base_url').$path;
}

# Transparently handles URL (with HTTP method and URI).
class ActionController_Path
{
  public $method;
  public $path;
  
  function __construct($method, $path)
  {
    $this->method = $method;
    $this->path   = '/'.$path;
  }
  
  function __toString()
  {
    return $this->path;
  }
}

# Transparently handles URL (with HTTP method and URI).
class ActionController_Url
{
  public $method;
  public $uri;
  
  function __construct($method, $uri)
  {
    $this->method = $method;
    $this->uri    = cfg::get('base_url').'/'.$uri;
  }
  
  function __toString()
  {
    return $this->uri;
  }
}

?>
