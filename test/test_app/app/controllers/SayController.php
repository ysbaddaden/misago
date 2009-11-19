<?php

class SayController extends ApplicationController
{
  function hello()
  {
    
  }
  
  function hello_who()
  {
    $this->who = isset($this->params[':id']) ? $this->params[':id'] : 'world';
  }
}

?>
