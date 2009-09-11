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
}

?>
