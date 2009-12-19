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
  
  function resource($name, $options=array(), $closure=null) {
    $this->_map('resource', $name, $options, $closure);
  }
  
  function resources($name, $options=array(), $closure=null) {
    $this->_map('resources', $name, $options, $closure);
  }
  
  private function _map($method, $name, $options, $closure)
  {
    if (is_object($options))
    {
      $closure = $options;
      $options = array();
    }
    if (!isset($options['name_prefix'])) {
      $options['name_prefix'] = $this->name_prefix;
    }
    if (!isset($options['path_prefix'])) {
      $options['path_prefix'] = $this->path_prefix;
    }
    if (!empty($this->ns))
    {
      $options['namespace'] = $this->ns.(
        empty($options['namespace']) ? '' : '\\'.$options['namespace']);
    }
    $this->map->$method($name, $options, $closure);
  }
}

?>
