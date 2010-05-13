<?php
require_once __DIR__.'/../../unit.php';
use Misago\ActionController;

class Test_ActionController_Base extends Misago\Unit\TestCase
{
  /*
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
  */
  
  function test_execute_and_render()
  {
    $controller = new SayController();
    $response   = new ActionController\AbstractResponse();
    
    ob_start();
    $controller->process(new ActionController\TestRequest(array(
      ':method'     => 'GET',
      ':controller' => 'say',
      ':action'     => 'hello',
      ':format'     => 'html',
      ':id'         => '123'
    )), $response);
    $this->assert_equal(trim($response->body), "<html><head></head><body><p>Hello world!</p></body></html>");
    
    $controller->process(new ActionController\TestRequest(array(
      ':method'     => 'GET',
      ':controller' => 'say',
      ':action'     => 'hello_who',
      ':format'     => 'html',
      ':id'         => '123'
    )), $response);
    $this->assert_equal(trim($response->body), "<html><head></head><body><p>Hello world!</p></body></html>");
    ob_get_clean();
  }
  
  function test_render()
  {
    $response   = new ActionController\AbstractResponse();
    $controller = new SayController();
    
    ob_start();
    $controller->process(new ActionController\TestRequest(array(':controller' => 'say', ':action' => 'hello')));
    ob_get_clean();
    
    $html = $controller->render_to_string('hello');
    $this->assert_equal(trim($html), "<html><head></head><body><p>Hello world!</p></body></html>");
    
    $html = $controller->render_to_string(array('action' => 'hello', 'layout' => 'basic'));
    $this->assert_equal(trim($html), "<html><body><p>Hello world!</p></body></html>");

    $html = $controller->render_to_string(array('action' => 'hello', 'format' => 'xml'));
    $this->assert_equal(trim($html), "<say><message>hello world</message>\n</say>", "action+controller layout+format");
  }
  
  function test_render_with_layout()
  {
    $response   = new ActionController\AbstractResponse();
    $controller = new SayController();
    ob_start();
    $controller->process(new ActionController\TestRequest(array(':controller' => 'say', ':action' => 'hello')));
    ob_get_clean();
    
    $html = $controller->render_to_string(array('layout' => 'basic'));
    $this->assert_equal(trim($html), "<html><body><p>Hello world!</p></body></html>", "particular layout");
    
    $controller = new ProductsController();
    ob_start();
    $controller->process(new ActionController\TestRequest(array(':controller' => 'products')));
    ob_get_clean();
    
    $html = $controller->render_to_string('index');
    $this->assert_equal(trim($html), "<html><body class=\"default\">products</body></html>", "default layout");
  }
  
  function test_render_xml()
  {
    $this->fixtures('products');
    $product = new Product();
    
    $controller = new ProductsController();
    ob_start();
    $controller->process(new ActionController\TestRequest(array(':controller' => 'products')));
    ob_get_clean();
    $xml = $controller->render_to_string(array('xml' => new Product(3)));
    $this->assert_equal($xml, "<?xml version=\"1.0\"?><product><id>3</id><name><![CDATA[azerty]]></name><price>6.95</price><created_at></created_at><updated_at></updated_at><in_stock>1</in_stock><description></description></product>", "single resource as XML");
    
    $products = $product->find(':all', array('select' => 'id,name', 'order' => 'id asc', 'limit' => 3));
    $xml = $controller->render_to_string(array('xml' => $products));
    $this->assert_equal($xml, "<?xml version=\"1.0\"?><products><product><id>1</id><name><![CDATA[bepo]]></name></product><product><id>2</id><name><![CDATA[qwerty]]></name></product><product><id>3</id><name><![CDATA[azerty]]></name></product></products>", "multiple resources as XML");
  }
  
  function test_render_json()
  {
    $this->fixtures('products');
    $product    = new Product();
    $controller = new ProductsController();
    ob_start();
    $controller->process(new ActionController\TestRequest(array(':controller' => 'products')));
    ob_get_clean();
    
    $json = $controller->render_to_string(array('json' => new Product(3)));
    $this->assert_equal($json, '{"id":3,"name":"azerty","price":6.95,"created_at":null,"updated_at":null,"in_stock":true,"description":null}', "single resource as JSON");
    
    $products = $product->find(':all', array('select' => 'id,name', 'order' => 'id asc', 'limit' => 3));
    $json = $controller->render_to_string(array('json' => $products));
    $this->assert_equal($json, '[{"id":1,"name":"bepo"},{"id":2,"name":"qwerty"},{"id":3,"name":"azerty"}]', "multiple resources as JSON");
  }
  
  function test_render_template()
  {
    $controller = new SayController();
    ob_start();
    $controller->process(new ActionController\TestRequest(array(':controller' => 'say', ':action' => 'hello')));
    ob_get_clean();
    
    $html = $controller->render_to_string(array('template' => 'errors/404'));
    $this->assert_equal(trim($html), '<html><head></head><body>404 not found</body></html>');
    
    $html = $controller->render_to_string(array('template' => 'errors/404', 'layout' => 'basic'));
    $this->assert_equal(trim($html), '<html><body>404 not found</body></html>');
    
    $html = $controller->render_to_string(array('template' => 'errors/404', 'layout' => false));
    $this->assert_equal(trim($html), '404 not found');
  }
  
  function test_url_for()
  {
    $c = new SayController();
    ob_start(); $c->process(new ActionController\TestRequest(array(':controller' => 'say', ':action' => 'hello'))); ob_get_clean();
    
    # uses the current request
    $options = array(':controller' => 'pages', ':action' => 'show', ':id' => 'toto', ':format' => 'json');
    $this->assert_equal($c->url_for($options), '/page/toto.json');
    
    $options = array(':controller' => 'pages', ':format' => 'xml', 'order' => 'asc');
    $this->assert_equal($c->url_for($options), '/pages.xml?order=asc');
    
    $options = array(':controller' => 'pages', ':format' => 'xml', 'order' => 'asc', 'path_only' => false);
    $this->assert_equal($c->url_for($options), 'http://localhost:3009/pages.xml?order=asc');
    
    $this->assert_equal($c->url_for(array(':controller' => 'admin', 'format' => 'html')), '/admin?format=html',
      "using query string parameter (string) sharing path parameter (symbol) name");
    
    # forces protocol, host & port
    $options = array(':controller' => 'pages', ':format' => 'xml', 'order' => 'asc', 'path_only' => false, 'host' => 'webcomics.fr', 'protocol' => 'https', 'port' => 80);
    $this->assert_equal($c->url_for($options), 'https://webcomics.fr/pages.xml?order=asc');
    
    $options = array(':controller' => 'stories', 'path_only' => false, 'host' => 'www.bd-en-ligne.fr');
    $this->assert_equal($c->url_for($options), 'http://www.bd-en-ligne.fr:3009/stories');
    
    # auto-fills holes in request
    $this->assert_equal($c->url_for(), '/say/hello');
    $this->assert_equal($c->url_for(array(':action' => 'blabla')), '/say/blabla');
    $this->assert_equal($c->url_for(array('page' => '2')), '/say/hello?page=2');
    $this->assert_equal($c->url_for(array('page' => '2')), '/say/hello?page=2');
    
    $c = new SayController();
    ob_start(); $c->process(new ActionController\TestRequest(array(':controller' => 'say', ':action' => 'hello'))); ob_get_clean();
    $this->assert_equal($c->url_for(array(':action' => 'index')), '/say');
    
    # with activerecords:
    $this->assert_equal((string)$c->url_for(new Product(2)), 'http://localhost:3009/products/2');
    $this->assert_equal((string)$c->url_for(new Product(3)), 'http://localhost:3009/products/3');
  }
}

?>
