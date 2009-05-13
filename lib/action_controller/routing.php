<?php

# @package ActionController
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
  
  /**
   * Singleton
   */
  static function draw()
  {
    if (self::$map === null) {
      self::$map = new self();
    }
    return self::$map;
  }
  
  /**
   * Empties the routes.
   */
  function reset()
  {
    $this->routes = array();
  }
  
  /**
   * Connects a path to a mapping.
   */
  function connect($path, $mapping=array())
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
    );
  }
  
  /**
   * Connects the homepage
   */
  function root(array $mapping)
  {
    foreach($this->routes as $i => $route)
    {
      if ($route['path'] == '') {
        unset($this->routes[$i]);
      }
    }
    $this->connect('', &$mapping);
  }
  
  /**
   * Builds RESTful connections
   */
  function resource($name)
  {
    $this->connect("$name.:format",             array(':controller' => $name, ':action' => 'index',  'conditions' => array('method' => 'GET')));
    $this->connect("$name/new.:format",         array(':controller' => $name, ':action' => 'neo',    'conditions' => array('method' => 'GET')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'show',   'conditions' => array('method' => 'GET')));
    $this->connect("$name/:id/edit.:format",    array(':controller' => $name, ':action' => 'edit',   'conditions' => array('method' => 'GET')));
    $this->connect("$name.:format",             array(':controller' => $name, ':action' => 'create', 'conditions' => array('method' => 'POST')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'update', 'conditions' => array('method' => 'PUT')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'delete', 'conditions' => array('method' => 'DELETE')));
  }
  
  /**
   * Returns a mapping for a given method+path.
   */
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
    
    unset($mapping['conditions']);
    unset($mapping['requirements']);
    
    $mapping[':method'] = $method;
    return $mapping;
  }
  
  /**
   * Returns a path for a given mapping.
   * 
   * FIXME: Handle special requirements for keys to select the route.
   */
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
          if ($this->has_keys(&$route, &$_mapping))
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
   * Creates helper functions to build paths and URL from routing definition (aka reverse routing).
   * 
   * For instance the route 'product/:id' => {:controller => 'products', :action => 'show'}
   * will make the 'show_product_path()' and 'show_product_url()' functions available.
   * 
   * IMPROVE: Recognize keys' special requirements (?)
   * OPTIMIZE: Cache generated code (into a PHP file on disk, or in memory with APC).
   * 
   * TODO: Handle :format if defined in route path or in mapping.
   */
  function build_path_and_url_helpers()
  {
    $functions = array();
    
    foreach($this->routes as $route)
    {
      if (isset($route['mapping'][':controller']))
      {
        # explicit controller
        $controllers = array($route['mapping'][':controller']);
      }
      elseif (strpos($route['path'], ':controller') !== false)
      {
        # implicit controller
        $controllers = $this->get_list_of_controllers();
      }
      
      foreach($controllers as $controller)
      {
        if (isset($route['mapping'][':action']))
        {
          # explicit action
          $actions = array($route['mapping'][':action']);
        }
        elseif (strpos($route['path'], ':action') !== false)
        {
          # implicit action
          $actions = $this->extract_actions_from_controller($controller);
        }
        if (empty($actions)) {
          continue;
        }
        
        $model = String::singularize($controller);
        
        foreach($actions as $action)
        {
          $func_base_name = ($action == 'index') ? $controller :
            (($action == 'neo') ? "new_{$model}" : "{$action}_{$model}");
          
          if (isset($functions["{$func_base_name}_path"])) {
            continue;
          }
          $functions["{$func_base_name}_path"] = $this->build_path_function($func_base_name, &$route, $controller, $action);
        }
      }
    }
    
    if (!empty($functions))
    {
      $functions = implode("\n\n", $functions);
      
      if ($_ENV['MISAGO_DEBUG'] == 3) {
        echo "\n\n$functions";
      }
      
      eval($functions);
    }
  }
  
  private function build_path_function($func_base_name, $route, $controller, $action)
  {
    $func = "function {$func_base_name}_path(\$keys=array())\n{\n".
      "  if (!is_array(\$keys)) {\n".
      "    \$keys = array(':id' => \$keys);\n".
      "  }\n";
    
    if (strpos($route['path'], ':controller') !== false) {
      $func .= "  \$keys[':controller'] = '$controller';\n";
    }
    if (strpos($route['path'], ':action') !== false) {
      $func .= "  \$keys[':action'] = '$action';\n";
    }
    $func .= "  \$path = strtr('{$route['path']}', \$keys);\n";
    
    $func .= $route['default'] ?
      "  \$path = preg_replace('/[\\/\\.\\?]:[^\\/\\.\\?]+/', '', \$path);\n" :
      "  \$path = preg_replace('/[\\/\\.\\?]:format/', '', \$path);\n";
    
    $method = isset($route['mapping']['conditions']['method']) ? $route['mapping']['conditions']['method'] : 'GET';
    $func .= "  return new ActionController_Path('{$method}', \$path);\n";
    $func .= "}";
    
    return $func;
  }
  
  /*
  private function build_url_function($func_base_name)
  {
    $func  = "function {$func_base_name}_url(\$keys=array()) {\n";
    $func .= "  return '/'.{$func_base_name}_path(\$keys);\n";
    $func .= "}";
    return $func;
  }
  */
  
  private function & get_list_of_controllers()
  {
    $controllers = array();
    
    $dh = opendir(APP.'/controllers/');
    if ($dh)
    {
      while(($file = readdir($dh)) !== false)
      {
        if (is_file(APP.'/controllers/'.$file)
          and strpos($file, '_controller.php'))
        {
          $controller = str_replace('_controller.php', '', $file);
          $controllers[] = $controller;
        }
      }
      closedir($dh);
    }
    return $controllers;
  }
  
  private function & extract_actions_from_controller($controller)
  {
    $methods = get_class_methods(String::camelize($controller.'_controller'));
    if (!empty($methods))
    {
      $inherited_methods = get_class_methods(get_parent_class(String::camelize($controller.'_controller')));
      $actions = array_diff($methods, $inherited_methods);
      return $actions;
    }
    return $methods;
  }
}


# Returns the path for a given mapping.
# 
# DEPRECATED: Is the 'path_for()' function necessary, or even useful?
# 
# <code>
# $path = path_for(array(':controller' => 'products'));
# $path = path_for('product_index');
# </code>
function path_for($mapping, array $keys=null)
{
  $map = ActionController_Routing::draw();
  if (is_array($mapping)) {
    return $map->reverse($mapping);
  }
  return $map->named_reverse($name, $keys);
}

# Transparently handles URL (with HTTP method and URI).
# @package ActionController
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

?>
