<?php
namespace Misago\ActionController\Routing;

# Transparently handles URL (with HTTP method and URI).
# :nodoc:
class Path
{
  public $method;
  public $path;
  
  function __construct($method, $path)
  {
    $this->method = $method;
    $this->path   = '/'.$path;
  }
  
  function __toString()
  {
    return $this->path;
  }
}

?>
