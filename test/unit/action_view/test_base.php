<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/lib/unit_test.php";
require_once "$location/test/test_app/config/boot.php";
require_once ROOT."/app/controllers/application.php";


# TODO: Test rendering a collection of partials {:collection => @products}.
class Test_ActionView_Base extends Unit_Test
{
  function test_render_view()
  {
    $view = new ActionView_Base();
    $view->view_path = 'say';
    
    $html = $view->render(array('action' => 'hello', 'format' => 'html'));
    $html = str_replace(array("\n", "\r"), '', trim($html));
    $this->assert_equal('passing options', trim($html), '<p>Hello world!</p>');
    
    $html = $view->render(array('action' => 'hello'));
    $html = str_replace(array("\n", "\r"), '', trim($html));
    $this->assert_equal('not passing format', trim($html), '<p>Hello world!</p>');
  }
  
  function test_render_view_with_layout()
  {
    $view = new ActionView_Base();
    $view->view_path = 'say';
    
    $xml = $view->render(array(
      'action' => 'hello',
      'layout' => 'say',
      'format' => 'xml',
    ));
    $xml = str_replace(array("\n", "\r"), '', trim($xml));
    $this->assert_equal('', $xml, "<say><message>hello world</message></say>");
  }
  
  function test_template_not_found()
  {
    $view = new ActionView_Base();
    $view->view_path = 'say';
    
    try
    {
      $view->render(array('action' => 'missing_action'));
      $result = true;
    }
    catch(MisagoException $e) {
      $result = false;
    }
    $this->assert_false('view template', $result);
    
    try
    {
      $view->render(array('partial' => 'missing_partial'));
      $result = true;
    }
    catch(MisagoException $e) {
      $result = false;
    }
    $this->assert_false('partial template', $result);
  }
  
  function test_copy_vars()
  {
    $controller = new SayController(array(
      ':action' => 'hello_who',
      ':id'     => 'my world',
    ));
    $controller->hello_who();
    
    $view = new ActionView_Base($controller);
    $view->render(array(
      'action' => 'hello_who',
      'format' => 'html',
    ));
    $this->assert_equal('', $controller->who, $view->who);
  }
  
  function test_yield()
  {
    $view = new ActionView_Base();
    $view->yield('content', 'some content');
    $this->assert_equal('', $view->yield('content'), 'some content');
  }
  
  function test_render_partial()
  {
    $view = new ActionView_Base();
    $view->view_path = 'say';
    $view->render(array('action' => 'hello'));
    
    # rendering a partial
    $html = $view->render(array('partial' => 'form'));
    $this->assert_equal('simple', trim($html), '<form></form>');
    
    # rendering a partial + passing some locals
    $html = $view->render(array(
      'partial' => 'form',
      'locals'  => array('var' => 'a string'),
    ));
    $this->assert_equal('passing locals', trim($html), '<form>a string</form>');
    
    # rendering a collection of partials
    $html = $view->render(array(
      'partial'    => 'collection',
      'collection' => array('aaa', 'bbb', 'ccc'),
    ));
    $html = str_replace(array("\r", "\n"), '', trim($html));
    $this->assert_equal('collection of partials', $html, '<li>1: aaa</li><li>2: bbb</li><li>3: ccc</li>');
    
    # rendering a shared partial
    $html = $view->render(array('partial' => 'ads/ad'));
    $this->assert_equal('shared partial', trim($html), '<ad></ad>');
  }
}

new Test_ActionView_Base();

?>
