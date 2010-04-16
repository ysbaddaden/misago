<?php
require_once __DIR__.'/../../unit.php';
require_once ROOT."/app/controllers/Application.php";
use Misago\ActionView;

class Test_ActionView_Base extends Misago\Unit\TestCase
{
  function test_render_template()
  {
    $view = new ActionView\Base();
    $view->view_path = 'say';
    
    $html = $view->render(array('template' => 'say/hello', 'format' => 'html', 'layout' => false));
    $html = str_replace(array("\n", "\r"), '', trim($html));
    $this->assert_equal(trim($html), '<p>Hello world!</p>');
    
    $html = $view->render(array('template' => 'say/hello', 'layout' => false));
    $html = str_replace(array("\n", "\r"), '', trim($html));
    $this->assert_equal(trim($html), '<p>Hello world!</p>', 'format defaults to html');
  }
  
  function test_template_not_found()
  {
    $view = new ActionView\Base();
    $view->view_path = 'say';
    
    try
    {
      $view->render(array('template' => 'say/missing_action'));
      $result = true;
    }
    catch(Misago\Exception $e) {
      $result = false;
    }
    $this->assert_false($result, 'rendered a view template');
    
    try
    {
      $view->render(array('partial' => 'missing_partial'));
      $result = true;
    }
    catch(Misago\Exception $e) {
      $result = false;
    }
    $this->assert_false($result, 'rendered a partial template');
  }
  
  function test_copy_vars()
  {
    $controller = new SayController(array(
      ':action' => 'hello_who',
      ':id'     => 'my world',
    ));
    $controller->hello_who();
    
    $view = new ActionView\Base($controller);
    $view->render(array(
      'template' => 'say/hello_who',
      'format'   => 'html',
    ));
    $this->assert_equal($controller->who, $view->who);
  }
  
  function test_yield()
  {
    $view = new ActionView\Base();
    $view->yield('content', 'some content');
    $this->assert_equal($view->yield('content'), 'some content');
  }
  
  function test_render_partial()
  {
    $view = new ActionView\Base();
    $view->view_path = 'say';
    $view->render(array('template' => 'say/hello'));
    
    # rendering a partial
    $html = $view->render(array('partial' => 'form'));
    $this->assert_equal(trim($html), '<form></form>');
    
    # rendering a partial + passing some locals
    $html = $view->render(array(
      'partial' => 'form',
      'locals'  => array('var' => 'a string'),
    ));
    $this->assert_equal(trim($html), '<form>a string</form>');
    
    # rendering a collection of partials
    $html = $view->render(array(
      'partial'    => 'collection',
      'collection' => array('aaa', 'bbb', 'ccc'),
    ));
    $html = str_replace(array("\r", "\n"), '', trim($html));
    $this->assert_equal($html, '<li>1: aaa</li><li>2: bbb</li><li>3: ccc</li>');
    
    # rendering a shared partial
    $html = $view->render(array('partial' => 'ads/ad'));
    $this->assert_equal(trim($html), '<ad></ad>');
    
    # rendering a collection of shared partials
    $view->view_path = 'ads';
    
    $html = $view->render(array(
      'partial'    => 'collection',
      'collection' => array('aaa', 'bbb', 'ddd'),
    ));
    $html = str_replace(array("\r", "\n"), '', trim($html));
    $this->assert_equal($html, '<li>1: aaa</li><li>2: bbb</li><li>3: ddd</li>');
    
    # with subdirs
    $html = $view->render(array('partial' => 'ads/big/ad'));
    $this->assert_equal(trim($html), '<big></big>');
  }
}

?>
