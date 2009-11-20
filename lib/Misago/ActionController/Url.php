<?php
namespace Misago\ActionController;

# Transparently handles URL (with HTTP method and URI).
class Url
{
  public $method;
  public $uri;
  
  function __construct($method, $uri)
  {
    $this->method = $method;
    $this->uri    = cfg_get('base_url').'/'.$uri;
  }
  
  function __toString()
  {
    return $this->uri;
  }
}

?>
