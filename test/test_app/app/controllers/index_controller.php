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
    $this->head(200);
    exit;
  }
  
  function forbidden()
  {
    $this->head(403);
    exit;
  }
  
  function redirected()
  {
    $this->redirect_to('/');
  }
  
  function test_head()
  {
    $this->head(array('location' => '/', 'status' => 410));
  }
  
  function cookie()
  {
    setcookie('misago', 'azerty');
    exit;
  }
  
  function html()
  {
    
  }
}

?>
