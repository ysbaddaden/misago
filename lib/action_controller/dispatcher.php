<?php

/*
function & ActionController_analyse_host()
{
  $protocol = 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : null).'://';
  
  $data = array(
    'protocol' => $protocol,
    'host'     => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
    'port'     => isset($_SERVER['HTTP_PORT']) ? $_SERVER['HTTP_PORT'] : null,
  );
  return $data;
}
*/

/*
/// TODO Use the 404 error binding instead. [done]
/// DEPRECATED
function ActionController_get_path()
{
  if (isset($_GET['misago_path']))
  {
	  $path = $_GET['misago_path'];
	  if (strpos($path, 'favicon.ico') !== false) {
		  die();
	  }
	  unset($_GET['misago_path']);
  }
  else {
	  $path = '';
  }
  return $path;
}
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
