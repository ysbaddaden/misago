<?php

$location = dirname(__FILE__).'/../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";
require_once ROOT."/app/controllers/application.php";

class Test_ActionController_Base extends Unit_TestCase
{
  function test_params()
  {
    $_GET  = array("test" => "1", "toto" => "brocoli"); 
    $_POST = array("test" => "2");
    
    $controller = new SayController();
    $this->assert_equal($controller->params, array('test' => '2', 'toto' => 'brocoli'));
    
    ob_start();
    $controller->execute(array(
      ':method' => 'GET',
      ':controller' => 'say',
      ':action' => 'hello',
      ':format' => 'html',
      ':id' => '123'
    ));
    ob_get_clean();
    $this->assert_equal($controller->params, array('test' => '2', 'toto' => 'brocoli', ':id' => '123'));
  }
  
  function test_execute_and_render()
  {
    $controller = new SayController();
    ob_start();
    $controller->execute(array(
      ':method' => 'GET',
      ':controller' => 'say',
      ':action' => 'hello',
      ':format' => 'html',
      ':id' => '123'
    ));
    $html = trim(ob_get_clean());
    $this->assert_equal($html, "<p>Hello world!</p>");
    
    $controller = new SayController();
    ob_start();
    $controller->execute('hello_who');
    $html = trim(ob_get_clean());
    $this->assert_equal($html, "<p>Hello world!</p>");

    $controller = new SayController();
    $html = $controller->render_string('hello');
    $this->assert_equal(trim($html), "<p>Hello world!</p>");

    $controller = new SayController();
    $html = $controller->render_string(array('action' => 'hello', 'layout' => 'basic'));
    $this->assert_equal(trim($html), "<html><body><p>Hello world!</p></body></html>");

    $controller = new SayController();
    $html = $controller->render_string(array('action' => 'hello', 'format' => 'xml'));
    $this->assert_equal(trim($html), "<say><message>hello world</message>\n</say>", "action+controller layout+format");
    
    $controller = new SayController();
    $controller->action = 'hello';
    $html = $controller->render_string(array('layout' => 'basic'));
    $this->assert_equal(trim($html), "<html><body><p>Hello world!</p></body></html>", "particular layout");
    
    
    $this->fixtures('products');
    $product = new Product();
    
    $controller = new SayController();
    $xml = $controller->render_string(array('xml' => new Product(3)));
    $this->assert_equal($xml, "<?xml version=\"1.0\"?><product><id>3</id><name><![CDATA[azerty]]></name><price>6.95</price><created_at></created_at><updated_at></updated_at><in_stock>1</in_stock><description></description></product>", "single resource as XML");
    
    $json = $controller->render_string(array('json' => new Product(3)));
    $this->assert_equal($json, '{"id":3,"name":"azerty","price":6.95,"created_at":null,"updated_at":null,"in_stock":true,"description":null}', "single resource as JSON");
    
    $products = $product->find(':all', array('select' => 'id,name', 'order' => 'id asc', 'limit' => 3));
    $xml = $controller->render_string(array('xml' => $products));
    $this->assert_equal($xml, "<?xml version=\"1.0\"?><products><product><id>1</id><name><![CDATA[bepo]]></name></product><product><id>2</id><name><![CDATA[qwerty]]></name></product><product><id>3</id><name><![CDATA[azerty]]></name></product></products>", "multiple resources as XML");
    
    $json = $controller->render_string(array('json' => $products));
    $this->assert_equal($json, '[{"id":1,"name":"bepo"},{"id":2,"name":"qwerty"},{"id":3,"name":"azerty"}]', "multiple resources as JSON");
  }
}

new Test_ActionController_Base();

?>
