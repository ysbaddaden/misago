<?php
namespace Misago;

# Generic exception handler for the framework.
class Exception extends \Exception
{
  protected $default_code = 500;
  
  function __construct($message, $code=null) {
    parent::__construct($message, $code ? $code : $this->default_code);
  }
}

?>
