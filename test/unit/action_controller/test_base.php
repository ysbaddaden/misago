<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once ROOT."/app/controllers/application.php";

class Test_ActionController_Base extends Unit_Test
{
  function test_params()
  {
    $_GET  = array("test" => "1", "toto" => "brocoli"); 
    $_POST = array("test" => "2");
    
    $controller = new SayController();
    $this->assert_equal("get+post", $controller->params,
      array('test' => '2', 'toto' => 'brocoli'));
    
    $controller = new SayController(array(
      ':method' => 'GET',
      ':controller' => 'say',
      ':action' => 'hello',
      ':format' => 'html',
      ':id' => '123'
    ));
    $this->assert_equal("get+post+params", $controller->params,
      array('test' => '2', 'toto' => 'brocoli', ':id' => '123'));
  }
  
  function test_execute_and_render()
  {
    
    $controller = new SayController(array(
      ':method' => 'GET',
      ':controller' => 'say',
      ':action' => 'hello',
      ':format' => 'html',
      ':id' => '123'
    ));
    ob_start();
    $controller->execute();
    $html = trim(ob_get_clean());
    $this->assert_equal("mapped action", $html, "<p>Hello world!</p>");

    $controller = new SayController();
    ob_start();
    $controller->execute('hello');
    $html = trim(ob_get_clean());
    $this->assert_equal("given action", $html, "<p>Hello world!</p>");
  }
}

new Test_ActionController_Base();

?>
