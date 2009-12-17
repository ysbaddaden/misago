<?php
namespace Misago\ActionController\Routing;

class Nested
{
  private $map;
  private $name_prefix;
  private $path_prefix;
  private $controller_prefix;
  
  function __construct($map, $name_prefix, $path_prefix, $controller_prefix='')
  {
    $this->map               = $map;
    $this->name_prefix       = $name_prefix;
    $this->path_prefix       = $path_prefix;
    $this->controller_prefix = $controller_prefix;
  }
  
  function resource($name, $options=array())
  {
    $options['name_prefix'] = $this->name_prefix;
    $options['path_prefix'] = $this->path_prefix;
    if ($this->controller_prefix) {
      $options['controller_prefix'] = $this->controller_prefix;
    }
    $options['path_prefix'] = $this->path_prefix;
    $this->map->resource($name, $options);
  }
  
  function resources($name, $options=array())
  {
    $options['name_prefix'] = $this->name_prefix;
    $options['path_prefix'] = $this->path_prefix;
    if ($this->controller_prefix) {
      $options['controller_prefix'] = $this->controller_prefix;
    }
    $this->map->resources($name, $options);
  }
}

?>
