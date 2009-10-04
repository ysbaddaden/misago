<?php

class ActionController_Dispatcher extends Object
{
  static function dispatch()
  {
    $request = new ActionController_CgiRequest();
    
    # routes
    $map = ActionController_Routing::draw();
    $mapping = $map->route(strtoupper($request->method()), $request->path());
    $map->build_path_and_url_helpers();
    
    # controller
    $name  = $mapping[':controller'].'_controller';
    $class = String::camelize($name);
    
    if (file_exists(ROOT."/app/controllers/$name.php"))
    {
      $controller = new $class($request);
      
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
}

?>
