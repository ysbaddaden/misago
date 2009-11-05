<?php

# Routing is what connects HTTP requests to your application's controllers,
# parsing actions an parameters too.
# 
# You do configure your application's routes in +config/routes.php+.
# A basic route config file looks like this:
# 
#   $map = ActionController_Routing::draw();
#   
#   # basic route: connects +/login+ to +AccountsController::login()+
#   $map->connect('login', array(
#     ':controller' => 'accounts',
#     ':action'     => 'login'
#   ));
#   
#   # landing page: +/+ => +HomeController::index()+
#   $map->root(array(':controller' => 'home'));
#   
#   # default route
#   $map->connect(':controller/:action/:id.:format');
# 
# You may use a different default route, for instance:
# 
#   $map->connect(':controller/:action.:format');
#   $map->connect(':controller/:id/:action.:format');
# 
# =Named routes
# 
# You may configure named routes, which will not only recognize the route
# but will also create some helper functions. For instance:
# 
#   $map->named('purchase', 'products/:id/purchase',
#     array(':controller' => 'order', ':action' => 'purchase'));
# 
# This will create the following functions: +purchase_path()+ and +purchase_url()+,
# which you may use like this:
# 
#   purchase_path(1);
#   purchase_path(array(':id' => 1));
#   purchase_path(new Product(1));
# 
# =Requirements (Regular Expressions)
# 
# You may check parameters by using regular expressions. A route that doesn't match
# requirements isn't matched. In this example +:id+ must be an integer:
# 
#   $map->connect('/posts/:id', array(':controller' => 'posts',
#     'requirements' => array(':id' => '\d+')));
# 
# =Conditions
# 
# You may add conditions to routes. At the moment only the HTTP method
# can be a condition. For instance the following route will only be used
# on a POST request:
# 
#   $this->connect('login', array(':controller' => 'accounts', ':action' => 'login',
#     'conditions' => array('method' => 'POST')));
# 
# Using named routes with conditions, returned URL will be an <tt>ActionController_Url</tt>
# or <tt>ActionController_Path</tt> object, that will be transparently handled by view's
# helpers to generate links and forms that will use the correct HTTP method.
# 
# =RESTful routes
# 
# REST webservices are handled transparently by misago.
# 
# Attention: in RESTful routes +:id+ must be an integer.
# 
# Example:
# 
#   $map->resource('posts');
# 
# This will create the following named routes:
# 
#   GET    /posts          => PostsController::index()
#   GET    /posts/new      => PostsController::neo()
#   POST   /posts          => PostsController::create()
#   GET    /posts/:id/edit => PostsController::edit()
#   GET    /posts/:id      => PostsController::show()
#   PUT    /posts/:id      => PostsController::update()
#   DELETE /posts/:id      => PostsController::delete()
# 
# Of course being named routes it also creates the following helper functions
# (they also exists with the +_url+ form):
# 
#   posts_path()       => GET    /posts
#   new_post_path()    => GET    /posts/new
#   create_post_path() => POST   /posts
#   show_post_path()   => GET    /posts/:id
#   edit_post_path()   => GET    /posts/:id/edit
#   update_post_path() => PUT    /posts/:id
#   delete_post_path() => DELETE /posts/:id
# 
# To create a REST resource, just generate it:
#
#   $ script/generate resource posts
# 
# This will create the full featured controller, the model and add the route
# to your configuration.
# 
# IMPROVE: cache routes (in APC).
# 
class ActionController_Routing extends Misago_Object
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
    if (self::$map === null)
    {
      self::$map = new self();
      require ROOT.'/config/routes.php';
    }
    return self::$map;
  }
  
  # Recognizes the route for the given request, an returns the associated controller.
  static function recognize($request)
  {
    $map = self::draw();
    $map->build_named_route_helpers();
    
    $params = $map->route(strtoupper($request->method()), $request->path());
    $request->path_parameters($params);
    
    $name  = $params[':controller'].'_controller';
    $class = String::camelize($name);
    
    if (!file_exists(ROOT."/app/controllers/$name.php")) {
      throw new MisagoException("No such controller $class.", 404);
    }
    
    $controller = new $class();
    return $controller;
  }
  
  # Empties routes.
  function reset()
  {
    $this->routes = array();
  }
  
  # Connects a path to a mapping.
  function connect($path, $mapping=array())
  {
    $this->connect_route(null, $path, $mapping);
  }
  
  # Connects the homepage.
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
    $this->named("show_$singular",   "$name/:id.:format",      array(':controller' => $name, ':action' => 'show',   'conditions' => array('method' => 'GET'),    'requirements' => array(':id' => '\d+')));
    $this->named("edit_$singular",   "$name/:id/edit.:format", array(':controller' => $name, ':action' => 'edit',   'conditions' => array('method' => 'GET'),    'requirements' => array(':id' => '\d+')));
    $this->named("create_$singular", "$name.:format",          array(':controller' => $name, ':action' => 'create', 'conditions' => array('method' => 'POST')));
    $this->named("update_$singular", "$name/:id.:format",      array(':controller' => $name, ':action' => 'update', 'conditions' => array('method' => 'PUT'),    'requirements' => array(':id' => '\d+')));
    $this->named("delete_$singular", "$name/:id.:format",      array(':controller' => $name, ':action' => 'delete', 'conditions' => array('method' => 'DELETE'), 'requirements' => array(':id' => '\d+')));
  }
  
  
  # Returns a mapping for a given method+path.
  # 
  # :private:
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
    
    foreach($mapping as $k => $v)
    {
      if (is_symbol($k) and is_string($v)) {
        $mapping[$k] = urldecode($v);
      }
    }
    unset($mapping['conditions']);
    unset($mapping['requirements']);
    
    $mapping[':method'] = $method;
    return $mapping;
  }
  
  # Returns a path for a given mapping.
  # 
  # FIXME: Handle special requirements for keys to select the route.
  # 
  # :private:
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
  
  # Builds the named routes helper functions.
  # 
  # :private:
  function build_named_route_helpers()
  {
    if (DEBUG
      or !file_exists(TMP.'/named_routes_helpers.php')
      or time() - strtotime('-24 hours') > filemtime(TMP.'/named_routes_helpers.php'))
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
      file_put_contents(TMP.'/named_routes_helpers.php', $contents);
    }
    
    include TMP.'/named_routes_helpers.php';
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
  
  # :nodoc:
  static function named_function_path(&$route, $keys)
  {
    $method = isset($route['mapping']['conditions']['method']) ?
      $route['mapping']['conditions']['method'] : 'GET';
    $path = self::named_function($route, $keys);
    return new ActionController_Path($method, $path);
  }
  
  # :nodoc:
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
          unset($keys[$k]);
        }
      }
      $query_string = http_build_query($query);
    }
    
    if (!isset($keys[':format']) and isset(self::$current_format)) {
      $keys[':format'] = self::$current_format;
    }
    
    foreach($keys as $k => $v) {
      $keys[$k] = urlencode($v);
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
  
  # Returns the full list of routes.
  static function collect()
  {
    $map = ActionController_Routing::draw();
    return $map->routes;
  }
}

# Resolves an URL (reverse routing).
# 
# Options:
# 
# - +anchor+: adds an anchor to the URL.
# - +path_only+: false to return an absolute URI, true to return the path only (defaults to true).
# - +protocol+: overwrites the current protocol.
# - +host+: overwrites the current host.
# - +port+: overwrites the current port.
# - +user+: username for HTTP login.
# - +password+: password for HTTP login.
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
# Using REST resources, you may pass an <tt>ActiveRecord</tt> directly. For instance:
# 
#   $product = new Product(43);
#   $url = url_for(product);    # => http://www.domain.com/products/3
# 
# IMPROVE: url_for: handle specified options (host, protocol, etc.)
# IMPROVE: url_for: permit for simplified calls, like url_for(array(':action' => 'index')), which shall use the current controller.
# 
# :namespace: ActionController
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
  sort($query_string);
  
  $path .= (empty($query_string) ? '' : '?'.implode('&', $query_string)).
    (empty($options['anchor']) ? '' : "#{$options['anchor']}");
  
  if ($options['path_only']) {
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
