<?php
namespace Misago\ActionController\Routing;

# Transparently handles URL (with HTTP method and URI).
# :nodoc:
class Url
{
  public $method;
  public $uri;
  
  function __construct($method, $uri)
  {
    $this->method = $method;
    $this->uri    = cfg_get('action_controller.base_url').'/'.$uri;
  }
  
  function __toString() {
    return $this->uri;
  }
}

?>
