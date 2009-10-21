<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../test/test_app/config/boot.php';

class Test_ActionController_Caching extends ActionController_TestCase
{
  function test_cache_store()
  {
    $controller = new SayController();
    $this->assert_instance_of($controller->cache, 'ActiveSupport_Cache_Store');
  }
  
  function test_caches_page()
  {
    $controller = new CachingController();
    $response   = new ActionController_AbstractResponse();
    
    # simple caching
    $controller->process(new ActionController_TestRequest(array(
      ':method' => 'GET', ':controller' => 'caching', ':action' => 'index', ':format' => 'html',
    )), $response);
    $this->assert_true(file_exists(ROOT.'/public/caching.html'));
    $controller->expire_page();
    $this->assert_false(file_exists(ROOT.'/public/caching.html'));
    
    # with particular format
    $controller->process(new ActionController_TestRequest(array(
      ':method' => 'GET', ':controller' => 'caching', ':action' => 'index', ':format' => 'xml',
    )), $response);
    $this->assert_true(file_exists(ROOT.'/public/caching.xml'));
    $controller->expire_page();
    $this->assert_false(file_exists(ROOT.'/public/caching.xml'));
  }
  
  function test_caches_page_unless()
  {
    $controller = new CachingController();
    $response   = new ActionController_AbstractResponse();
    
    # unless condition (fails)
    $controller->process(new ActionController_TestRequest(array(
      ':method' => 'GET', ':controller' => 'caching', ':action' => 'show', ':id' => 2, ':format' => 'html',
    )), $response);
    $this->assert_false(file_exists(ROOT.'/public/caching/show/2.html'));
    
    # with format and unless condition (success)
    $controller->process(new ActionController_TestRequest(array(
      ':method' => 'GET', ':controller' => 'caching', ':action' => 'show', ':id' => 45, ':format' => 'xml',
    )), $response);
    $this->assert_true(file_exists(ROOT.'/public/caching/show/45.xml'));
    $controller->expire_page();
    $this->assert_false(file_exists(ROOT.'/public/caching/show/45.xml'));
  }
  
  function test_caches_page_if()
  {
    $controller = new CachingController();
    $response   = new ActionController_AbstractResponse();
    
    # if condition (fails)
    $controller->process(new ActionController_TestRequest(array(
      ':method' => 'GET', ':controller' => 'caching', ':action' => 'feed', ':format' => 'json',
    )), $response);
    $this->assert_false(file_exists(ROOT.'/public/caching/feed.json'));
    $controller->expire_page();
    
    # if condition (success)
    $controller->process(new ActionController_TestRequest(array(
      ':method' => 'GET', ':controller' => 'caching', ':action' => 'feed', ':format' => 'xml',
    )), $response);
    $this->assert_true(file_exists(ROOT.'/public/caching/feed.xml'));
    $controller->expire_page();
    $this->assert_false(file_exists(ROOT.'/public/caching/feed.xml'));
  }
  
  function test_caches_action()
  {
    
  }
}

new Test_ActionController_Caching();

?>
