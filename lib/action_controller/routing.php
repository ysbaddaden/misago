<?php

# TODO: Polymorphic URL Generation
# TODO: Named routes
class ActionController_Routing extends Object
{
  private $routes          = array();
  private $default_mapping = array(
    ':method'      => 'GET',
    ':controller'  => 'index',
    ':action'      => 'index',
    ':format'      => 'html',
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
      '#(?<![\/\.\?]):([\w_-]+)#u' => '(?<$1>[^\/\.\?]*)?',           # :param
      '#([\/\.\?]):([\w_-]+)#u'    => '(?:\\\$1(?<$2>[^\/\.\?]*))?',  # [/.?]:param
      '#([\/\.\?])\*([\w_-]+)#u'   => '(?:\\\$1(?<$2>.*?))?',         # [/.?]*path
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
      'path'     => $path,
      'regexp'   => "#^$regexp$#u",
      'mapping'  => &$mapping,
      'keys'     => &$keys,
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
    $this->connect("$name.:format",             array(':controller' => $name, ':action' => 'index',   'conditions' => array('method' => 'GET')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'show',    'conditions' => array('method' => 'GET')));
    $this->connect("$name/:id/:action.:format", array(':controller' => $name,                         'conditions' => array('method' => 'GET')));
    $this->connect("$name.:format",             array(':controller' => $name, ':action' => 'create',  'conditions' => array('method' => 'POST')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'update',  'conditions' => array('method' => 'PUT')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'destroy', 'conditions' => array('method' => 'DELETE')));
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
   * FIXME: Handle special requirements for keys.
   * TODO: What about HTTP method calls?
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
            $path = strtr($route['path'], $_mapping);
            $path = str_replace(array('/:format', '.:format', '?:format'), '', $path);
            return "/$path";
          }
          
          # default
          $path = ($_mapping[':action'] == 'index') ?
            "/{$_mapping[':controller']}" :
            "/{$_mapping[':controller']}/{$_mapping[':action']}";
          return isset($_mapping[':format']) ? "$path.{$_mapping[':format']}" : $path;
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
   * Creates helper functions like 'edit_product_url()' for easy path and url generations.
   */
  function build_path_and_url_helpers()
  {
    $functions = '';
    
    foreach($this->routes as $route)
    {
      if (strpos($route['mapping'][':controller'], ':controller'))
      {
        # implicit controllers: let's get them
        $controllers = $this->get_list_of_controllers();
      }
      elseif (isset($route['mapping'][':controller']))
      {
        # explicit controller
        $controllers = array($route['mapping'][':controller']);
      }
      
      foreach($controllers as $controller)
      {
        if (isset($route['mapping'][':action']))
        {
          # explicit action
          $actions = array($route['mapping'][':action']);
        }
        elseif (strpos($route['path'], ':action'))
        {
          # implicit actions: let's get them
          $actions = $this->extract_actions_from_controller($controller);
        }
        
        $model = String::singularize($controller);
        
        foreach($actions as $action)
        {
          $func_base_name = ($action == 'index') ? $controller : "{$action}_{$model}";
          
          $functions .= "\nfunction {$func_base_name}_path(\$keys=null) {\n".
            "return strtr('{$route['path']}', \$keys);\n".
            "}";
          $functions .= "\nfunction {$func_base_name}_url(\$keys=null) { \n".
            "return FULL_BASE_URL.{$func_base_name}_path(\$keys);\n".
            "}";
        }
      }
    }
    eval($functions);
  }
  
  private function & get_list_of_controllers()
  {
    $controllers = array();
    
    $dh = opendir(ROOT.'/app/controllers/');
    if ($dh)
    {
      while(($file = readdir($dh)) !== false)
      {
        if (is_file(ROOT.'/app/controllers/'.$file) {
          $controllers[] = str_replace('.php', '', $controller);
        }
      }
      closedir($dh);
    }
    return $controllers;
  }
  
  private function & extract_actions_from_controller($controller)
  {
    require_once "controllers/$controller.php";
    $actions = get_class_methods(String::camelize($controller));
    return $actions;
  }
}

/**
 * Returns the path for a given mapping.
 * 
 * <code>
 * $path = path_for(array(':controller' => 'products'));
 * $path = path_for('product_index');
 * </code>
 */
function path_for($mapping, array $keys=null)
{
  $map = ActionController_Routing::draw();
  if (is_array($mapping)) {
    return $map->reverse($mapping);
  }
  return $map->named_reverse($name, $keys);
}

?>
