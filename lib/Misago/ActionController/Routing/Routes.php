<?php
namespace Misago\ActionController\Routing;
use Misago\ActiveSupport\String;

# Routing is what connects HTTP requests to your application's controllers,
# parsing actions an parameters too.
# 
# You do configure your application's routes in +config/routes.php+.
# A basic route config file looks like this:
# 
#   Misago\ActionController\Routing\Routes::draw(function()
#   {
#     # basic route: connects /login to AccountsController::login()
#     $map->connect('login', array(
#      ':controller' => 'accounts',
#      ':action'     => 'login'
#     ));
#     
#     # landing page: / => HomeController::index()
#     $map->root(array(':controller' => 'home'));
#     
#     # default routes
#     $map->connect(':controller/:action/:id');
#     $map->connect(':controller/:action/:id.:format');
#   });
# 
# You may use different default routes, but it's not recommended.
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
# You may check parameters by using regular expressions. A route that doesn't
# match the requirements isn't matched. In this example +:id+ must be an integer:
# 
#   $map->connect('/posts/:id', array(':controller' => 'posts',
#     'requirements' => array(':id' => '\d+')));
# 
# =Conditions
# 
# You may add conditions to routes. At the moment only the HTTP method
# can be a condition. For instance the following route will match on a +POST+
# request only:
# 
#   $this->connect('login', array(
#     ':controller' => 'accounts',
#     ':action'     => 'login',
#     'conditions'  => array('method' => 'POST')
#   ));
# 
# Using named routes with conditions, the returned URL will be a
# <tt>Misago\ActionController\Routing\Url</tt> or
# <tt>Misago\ActionController\Routing\Path</tt> object, that will be
# transparently handled by view helpers to generate links and forms that will
# use the correct HTTP method.
# 
class Routes extends ResourceRoutes
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
  
  private $built_named_route_helpers = false;
  
  # Singleton
  static function draw($closure=null)
  {
    if (self::$map === null) {
      self::$map = new self();
    }
    if ($closure !== null) {
      $closure(self::$map);
    }
    return self::$map;
  }
  
  # IMPROVE: Cache routes in APC.
  # :nodoc:
  static function boot()
  {
    require ROOT."/config/routes.php";
    $map = static::draw();
    $map->build_named_route_helpers();
  }
  
  # Recognizes the route for a request, and returns a controller.
  static function recognize($request)
  {
    $map = self::draw();
    
    $params = $map->route(strtoupper($request->method()), $request->path());
    $request->path_parameters($params);
    
    $name = $params[':controller'].'_controller';
    if (strpos($name, '\\') === false) {
      $class = String::camelize($name);
    }
    else
    {
      $parts = explode('\\', $name);
      $class = '';
      foreach($parts as $part) {
        $class .= "\\".String::camelize($part);
      }
      unset($parts);
    }
    
    if (!file_exists(ROOT."/app/controllers/".str_replace('\\', '/', $class).".php")) {
      throw new \Misago\Exception("No such controller $class.", 404);
    }
    
    $controller = new $class();
    return $controller;
  }
  
  # Empties routes.
  function reset() {
    $this->routes = array();
  }
  
  # Connects a path to a mapping.
  function connect($path, $mapping=array()) {
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
  function named($name, $path, $mapping=array()) {
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
      if (strpos($key, ':') === 0) {
        $keys[] = $key;
      }
    }

    if (isset($mapping['conditions']['method'])) {
      $mapping['conditions']['method'] = strtoupper($mapping['conditions']['method']);
    }
    sort($keys);
    
    $this->routes[] = array(
      'path'    => $path,
      'regexp'  => "#^$regexp$#u",
      'mapping' => $mapping,
      'keys'    => $keys,
      'default' => empty($mapping),
      'name'    => $name,
    );
  }
  
  protected function named_route_exists($name)
  {
    foreach($this->routes as $route)
    {
      if ($route['name'] == $name) {
        return true;
      }
    }
    return false;
  }
  
  # Returns a mapping for a given method+path.
  # 
  # :nodoc:
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
      throw new \Misago\Exception("No route for '$method /$uri'", 404);
    }
    
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
  # :nodoc:
  function reverse($mapping)
  {
    if (!isset($mapping[':action'])) $mapping[':action'] = 'index';
    $keys = array_diff(array_keys($mapping), array(':controller', ':action'));
    $k    = array_keys($mapping);
    sort($k);
    
    foreach($this->routes as $route)
    {
      # matches specific routes
      if (isset($route['mapping'][':controller'])
        and $mapping[':controller'] == $route['mapping'][':controller']
        and (
          (isset($route['mapping'][':action']) and $mapping[':action'] == $route['mapping'][':action'])
          or !isset($route['mapping'][':action'])
        ))
      {
        $diff = array_diff($keys, $route['keys']);
        if (empty($diff))
        {
          $path   = strtr($route['path'], $mapping);
          $method = isset($route['mapping']['conditions']['method']) ?
            $route['mapping']['conditions']['method'] : 'GET';
          return new Path($method, $path);
        }
      }
      
      # matches default routes
      if ($route['keys'] == $k)
      {
        $path   = strtr($route['path'], $mapping);
        $method = isset($route['mapping']['conditions']['method']) ?
          $route['mapping']['conditions']['method'] : 'GET';
        return new Path($method, $path);
      }
    }
    
    # default default routes
    if ($this->compare($mapping, array(':controller', ':action')))
    {
      $path = ($mapping[':action'] != 'index') ?
        "{$mapping[':controller']}/{$mapping[':action']}" : $mapping[':controller'];
      return new Path('GET', $path);
    }
    elseif ($this->compare($mapping, array(':controller', ':action', ':format')))
    {
      $path = ($mapping[':action'] != 'index') ?
        "{$mapping[':controller']}/{$mapping[':action']}" : $mapping[':controller'];
      return new Path('GET', "$path.{$mapping[':format']}");
    }
    elseif ($this->compare($mapping, array(':controller'))) {
      return new Path('GET', $mapping[':controller']);
    }
    elseif ($this->compare($mapping, array(':controller', ':format'))) {
      return new Path('GET', "{$mapping[':controller']}.{$mapping[':format']}");
    }
    
    throw new \Misago\Exception("No route for: ".print_r($mapping, true), 500);
  }
  
  private function compare($mapping, $keys)
  {
    $ary = array_diff(array_keys($mapping), $keys);
    return empty($ary);
  }
  
  # Builds the named routes helper functions.
  # 
  # :nodoc:
  function build_named_route_helpers()
  {
    if ($this->built_named_route_helpers) {
      return;
    }
    
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
    $this->built_named_route_helpers = true;
  }
  
  private function build_named_function($type, &$route)
  {
    $exported_route = var_export($route, true);
    $func = "function {$route['name']}_{$type}(\$keys=array())
    {
      \$route = $exported_route;
      return \Misago\ActionController\Routing\Routes::named_function_{$type}(\$route, \$keys);
    }";
    return $func;
  }
  
  # :nodoc:
  static function named_function_path(&$route, $keys)
  {
    $method = isset($route['mapping']['conditions']['method']) ?
      $route['mapping']['conditions']['method'] : 'GET';
    $path = self::named_function($route, $keys);
    return new Path($method, $path);
  }
  
  # :nodoc:
  static function named_function_url(&$route, $keys)
  {
    $method = isset($route['mapping']['conditions']['method']) ?
      $route['mapping']['conditions']['method'] : 'GET';
    $path = self::named_function($route, $keys);
    return new Url($method, $path);
  }
  
  static private function named_function(&$route, $keys)
  {
    if (is_object($keys) and $keys instanceof \Misago\ActiveRecord\Record)
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
      ksort($query);
      $query_string = http_build_query($query);
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
    return $path.(empty($query_string) ? '' : "?{$query_string}");
  }
  
  # Returns the full list of routes.
  static function collect()
  {
    $map = self::draw();
    return $map->routes;
  }
}

?>
