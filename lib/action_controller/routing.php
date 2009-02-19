<?php

/// TODO: Reverse Routing: Mapping => URL
/// TODO: Polymorphic URL Generation
/// TODO: Named routes
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
  
  /// Singleton
  static function draw()
  {
    if (self::$map === null) {
      self::$map = new self();
    }
    return self::$map;
  }
  
  function reset()
  {
    $this->routes = array();
  }
  
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
    
    $this->routes[] = array(
      'path'     => $path,
      'regexp'   => "#^$regexp$#u",
      'mapping'  => &$mapping,
    );
  }
  
  /// Connects the homepage
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
  
  /// Builds RESTful connections
  function resource($name)
  {
    $this->connect("$name.:format",             array(':controller' => $name, ':action' => 'index',   'conditions' => array('method' => 'GET')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'show',    'conditions' => array('method' => 'GET')));
    $this->connect("$name/:id/:action.:format", array(':controller' => $name,                         'conditions' => array('method' => 'GET')));
    $this->connect("$name.:format",             array(':controller' => $name, ':action' => 'create',  'conditions' => array('method' => 'POST')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'update',  'conditions' => array('method' => 'PUT')));
    $this->connect("$name/:id.:format",         array(':controller' => $name, ':action' => 'destroy', 'conditions' => array('method' => 'DELETE')));
  }
  
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
  
  function reverse(array $mapping)
  {
    $mapping = array_merge(array(
      ':controller' => '',
      ':action'     => '',
      ':format'     => '',
    ), $mapping);
    
    print_r($mapping);
    
    foreach($this->routes as $route)
    {
      print_r($route);
    /*
      if ($route[':controller'] == $mapping[':controller']
        and $route[':action'] == $mapping[':action'])
      {
        return $route['path'];
      }
     */
    }
    return '/';
  }
}

?>
