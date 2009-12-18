<?php
namespace Misago\ActionController\Routing;

class Nested
{
  private $map;
  private $name_prefix;
  private $path_prefix;
  private $ns;
  
  function __construct($map, $name_prefix, $path_prefix, $ns='')
  {
    $this->map         = $map;
    $this->name_prefix = $name_prefix;
    $this->path_prefix = $path_prefix;
    $this->ns          = $ns;
  }
  
  function resource($name, $options=array())
  {
    $options['name_prefix'] = $this->name_prefix;
    $options['path_prefix'] = $this->path_prefix;
    if (!empty($this->ns))
    {
      $options['namespace'] = $this->ns.(
        empty($options['namespace']) ? '' : '\\'.$options['namespace']);
    }
    $this->map->resource($name, $options);
  }
  
  function resources($name, $options=array())
  {
    $options['name_prefix'] = $this->name_prefix;
    $options['path_prefix'] = $this->path_prefix;
    if (!empty($this->ns))
    {
      $options['namespace'] = $this->ns.(
        empty($options['namespace']) ? '' : '\\'.$options['namespace']);
    }
    $this->map->resources($name, $options);
  }
}

?>
