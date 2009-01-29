<?php

$location = dirname(__FILE__).'/../../..';
require_once "$location/lib/misago_exception.php";
require_once "$location/lib/unit_test.php";
require_once "$location/lib/action_controller/routing.php";

class Test_ActionController_Routing extends Unit_Test
{
  function test_draw()
  {
    $map = ActionController_Routing::draw();
    $this->assert_equal('', get_class($map), 'ActionController_Routing');
  }
  
  function test_map_root()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    
    $map->root(array(':controller' => 'welcome', ':action' => 'home'));
    $this->assert_equal('GET /', $map->route('GET', ''), array(
      ':method'     => 'GET',
      ':controller' => 'welcome',
      ':action'     => 'home',
      ':format'     => 'html',
    ));
    
    $map->root(array(':controller' => 'welcome', ':action' => 'home', ':format' => 'xml'));
    $this->assert_equal('GET /.xml', $map->route('GET', ''), array(
      ':method'     => 'GET',
      ':controller' => 'welcome',
      ':action'     => 'home',
      ':format'     => 'xml',
    ));
  }
  
  function test_map_default_connect()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    
    $map->connect(':controller/:action/:id.:format');
    
    $this->assert_equal('GET /posts/show/1', $map->route('GET', 'posts/show/1'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('POST /posts/edit/1.xml', $map->route('POST', 'posts/edit/1.xml'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal('GET /posts/index.json', $map->route('GET', 'posts.json'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => 'json',
    ));
    
    $this->assert_equal('POST /posts.json', $map->route('POST', 'posts.json'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => 'json',
    ));
  }
  
  function test_map_connect()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    
    $map->connect('posts.:format', array(':controller' => 'posts', ':action' => 'index'));
    $map->connect('posts/:id.:format', array(':controller' => 'posts', ':action' => 'show'));
    $map->connect('posts/:id/:action.:format', array(':controller' => 'posts'));
    
    $this->assert_equal('GET /posts/1', $map->route('GET', 'posts/1'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('GET /posts/1.xml', $map->route('GET', 'posts/1.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal('POST /posts/1/create', $map->route('POST', 'posts/1/create'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'create',
      ':id'         => '1',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('POST /posts/1/edit.xml', $map->route('POST', 'posts/1/edit.xml'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal('GET /posts.xml', $map->route('GET', 'posts.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => 'xml',
    ));
  }
  
  function test_map_resource()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    
    $map->resource('posts');
    
    $this->assert_equal('GET /posts', $map->route('GET', 'posts'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('GET /posts/1', $map->route('GET', 'posts/1'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('GET /posts/1/neo', $map->route('GET', 'posts/1/neo'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'neo',
      ':id'         => '1',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('GET /posts/5/edit.html', $map->route('GET', 'posts/5/edit.html'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'edit',
      ':id'         => '5',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('POST /posts.xml', $map->route('POST', 'posts.xml'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'create',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal('PUT /posts/7', $map->route('PUT', 'posts/7'), array(
      ':method'     => 'PUT',
      ':controller' => 'posts',
      ':action'     => 'update',
      ':id'         => '7',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('DELETE /posts/1.json', $map->route('DELETE', 'posts/1.json'), array(
      ':method'     => 'DELETE',
      ':controller' => 'posts',
      ':action'     => 'destroy',
      ':id'         => '1',
      ':format'     => 'json',
    ));
  }
  
  function test_route_globbing()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->connect('help/*path.:format', array(':controller' => 'html_pages', ':action' => 'help'));

    $this->assert_equal('/help/webcomics/pages/create', $map->route('GET', 'help/webcomics/pages/create'), array(
      ':method'     => 'GET',
      ':controller' => 'html_pages',
      ':action'     => 'help',
      ':path'       => 'webcomics/pages/create',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('/help/webcomics/pages/create.xml', $map->route('GET', 'help/webcomics/pages/create.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'html_pages',
      ':action'     => 'help',
      ':path'       => 'webcomics/pages/create',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal('/help/webcomics/pages.old/create.xml', $map->route('GET', 'help/webcomics/pages.old/create.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'html_pages',
      ':action'     => 'help',
      ':path'       => 'webcomics/pages.old/create',
      ':format'     => 'xml',
    ));
  }
  
  function test_route_requirements()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->connect('page/:id.:format', array(':controller' => 'pages', ':action' => 'show',
      'requirements' => array(':id' => '\d+')));
    
    $this->assert_equal('/page/456', $map->route('GET', 'page/456'), array(
      ':method'     => 'GET',
      ':controller' => 'pages',
      ':action'     => 'show',
      ':id'         => '456',
      ':format'     => 'html',
    ));
    
    $this->assert_equal('/page/456.xml', $map->route('GET', 'page/456.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'pages',
      ':action'     => 'show',
      ':id'         => '456',
      ':format'     => 'xml',
    ));
    
    try {
      $map->route('GET', 'page/test456');
    }
    catch(Exception $e) {
      $this->assert_true('/page/test456', true);
    }
  }
}

new Test_ActionController_Routing();

?>
