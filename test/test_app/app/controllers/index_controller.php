<?php

class IndexController extends ApplicationController
{
  function get()
  {
    print_r($_GET);
    exit;
  }
  
  function post()
  {
    unset($_POST['_method']);
    print_r($_POST);
    exit;
  }
  
  function files()
  {
    print_r($_FILES);
    exit;
  }
  
  function index()
  {
    HTTP::status(200);
    exit;
  }
  
  function forbidden()
  {
    HTTP::status(403);
    exit;
  }
  
  function redirected()
  {
    $this->redirect_to('/');
  }
  
  function cookie()
  {
    setcookie('misago', 'azerty');
    exit;
  }
}

?>
