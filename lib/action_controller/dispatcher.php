<?php
/**
 * 
 * @package ActionController
 */
function ActionController_dispatch($method, $uri)
{
  # route
  $map     = ActionController_Routing::draw();
  $mapping = $map->route($method, $uri);
  
  # controller
  $name  = $mapping[':controller'].'_controller';
  $class = String::camelize($name);
  
  if (file_exists(ROOT."/app/controllers/$name.php"))
  {
    require 'application.php';
    require ROOT."/app/controllers/$name.php";
    $controller = new $class($mapping);
    
    # action!
    if (method_exists($controller, $mapping[':action']))
    {
      if ($mapping[':action'] != 'execute'
        and strpos($mapping[':action'], '__') !== 0
        and is_callable(array($controller, $mapping[':action'])))
      {
        $controller->execute($mapping[':action']);
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

?>
