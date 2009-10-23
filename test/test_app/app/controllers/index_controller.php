<?php

class IndexController extends ApplicationController
{
  function get()
  {
    $this->render(array('text' => print_r($_GET, true)));
  }
  
  function post()
  {
    unset($_POST['_method']);
    $this->render(array('text' => print_r($_POST, true)));
  }
  
  function files()
  {
    $this->render(array('text' => print_r($_FILES, true)));
  }
  
  function index()
  {
    $this->head(200);
  }
  
  function forbidden()
  {
    $this->head(403);
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
    $this->render(array('text' => ''));
  }
  
  function html()
  {
    
  }
  
  function errors()
  {
    switch($this->format)
    {
      case 'html': $this->render(array('text' => "<h1>error</h1>\n",   'status' => 412)); break;
      case 'xml':  $this->render(array('text' => "<xml>error</xml>\n", 'status' => 412)); break;
      case 'xml':  $this->render(array('text' => "<xml>error</xml>\n", 'status' => 412)); break;
    }
  }
}

?>
