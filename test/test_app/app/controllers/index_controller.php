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
    print_r($_POST);
    exit;
  }
}

?>
