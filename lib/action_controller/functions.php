<?php
# Dispatches the current request.
# 
# This will route the request, make some preparations
# (eg: build route functions)build controller and run
# the action.
# 
# @namespace ActionController
function ActionController_dispatch($method, $uri)
{
  # route
  $map     = ActionController_Routing::draw();
  $mapping = $map->route($method, $uri);
  
  $map->build_path_and_url_helpers();
  
  # controller
  $name  = $mapping[':controller'].'_controller';
  $class = String::camelize($name);
  
  if (file_exists(ROOT."/app/controllers/$name.php"))
  {
    $controller = new $class();
    
    # action!
    if (method_exists($controller, $mapping[':action']))
    {
      if ($mapping[':action'] != 'execute'
        and strpos($mapping[':action'], '__') !== 0
        and is_callable(array($controller, $mapping[':action'])))
      {
        $controller->execute($mapping);
      }
      else {
        throw new MisagoException("Tried to call a private/protected method as a public action: {$mapping[':action']}", 400);
      }
    }
    else {
      throw new MisagoException("No such action: $class->{$mapping[':action']}", 404);
    }
  }
  else {
    throw new MisagoException("No such controller: {$class}", 404);
  }
}

# Analyzes host, in order to produce some URL.
# 
# @namespace ActionController
function ActionController_host_analyzer()
{
  if (isset($_SERVER['HTTP_HOST']))
  {
    $protocol  = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'];
    $base_path = isset($_SERVER['REDIRECT_URI']) ? dirname($_SERVER['REDIRECT_URI']) : '';
    cfg::set('base_url', "{$protocol}://{$host}".(($base_path == '/') ? '' : $base_path));
  }
}

?>
