<?php

class RequestForgeryController extends Misago\ActionController\Base
{
  function __construct()
  {
    cfg_set('action_controller.allow_forgery_protection', true);
    parent::__construct();
    static::protect_from_forgery();
  }
  
  function get_token()
  {
    $this->render(array('text' => Misago\ActionController\form_authenticity_token()));
  }
  
  function test()
  {
    $this->render(array('status' => 200, 'text' => print_r($_SESSION, true)));
  }
  
  function __destruct()
  {
    cfg_set('action_controller.allow_forgery_protection', false);
  }
}

?>
