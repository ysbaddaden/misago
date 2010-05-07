<?php
namespace Misago\ActionController\Routing;

# :nodoc:
class Nested
{
  private $map;
  private $name_prefix;
  private $path_prefix;
  private $name_space;
  
  function __construct($map, $name_prefix, $path_prefix, $name_space=null)
  {
    $this->map         = $map;
    $this->name_prefix = $name_prefix;
    $this->path_prefix = $path_prefix;
    $this->name_space  = $name_space;
  }
  
  function resources($name, $options=array(), $closure=null) {
    $this->map('resources', $name, $options, $closure);
  }
  
  function resource($name, $options=array(), $closure=null) {
    $this->map('resource', $name, $options, $closure);
  }
  
  private function map($method, $name, $options, $closure)
  {
    if (is_object($options))
    {
      $closure = $options;
      $options = array();
    }
    if (!isset($options['name_prefix'])) $options['name_prefix'] = $this->name_prefix;
    if (!isset($options['path_prefix'])) $options['path_prefix'] = $this->path_prefix;
    
    if (!empty($this->name_space))
    {
      $options['name_space'] = $this->name_space.(
        empty($options['name_space']) ? '' : '\\'.$options['name_space']);
    }
    $this->map->$method($name, $options, $closure);
  }
}

?>
