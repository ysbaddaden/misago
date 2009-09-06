<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
$location = dirname(__FILE__).'/../../..';

require_once "$location/test/test_app/config/boot.php";

class Test_ActionController_Routing extends Unit_TestCase
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
      ':format'     => null,
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
      ':format'     => null,
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
      ':format'     => null,
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
      ':format'     => null,
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
    $map->connect(':controller/:action/:id.:format');
    
    $this->assert_equal('GET /posts', $map->route('GET', 'posts'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => null,
    ));
    
    $this->assert_equal('GET /posts/1', $map->route('GET', 'posts/1'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal('GET /posts/new', $map->route('GET', 'posts/new'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'neo',
      ':format'     => null,
    ));
    
    $this->assert_equal('GET /posts/1/edit', $map->route('GET', 'posts/1/edit'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal('POST /posts', $map->route('POST', 'posts'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'create',
      ':format'     => null,
    ));
    
    $this->assert_equal('PUT /posts/1', $map->route('PUT', 'posts/1'), array(
      ':method'     => 'PUT',
      ':controller' => 'posts',
      ':action'     => 'update',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal('DELETE /posts/1', $map->route('DELETE', 'posts/1'), array(
      ':method'     => 'DELETE',
      ':controller' => 'posts',
      ':action'     => 'delete',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal('GET /posts/widget', $map->route('GET', 'posts/widget'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'widget',
      ':format'     => null,
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
      ':format'     => null,
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
      ':format'     => null,
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
  
  function test_reverse()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->resource('posts');
    $map->connect('page/:id.:format', array(':controller' => 'pages', ':action' => 'show'));
    $map->connect(':controller/:action/:id.:format');
    
    $mapping = array(':controller' => 'pages', ':action' => 'show', ':id' => 'toto');
    $this->assert_equal("build route", (string)$map->reverse($mapping), '/page/toto');
    
    $mapping = array(':controller' => 'products', ':action' => 'edit', ':id' => 2);
    $this->assert_equal("build route", (string)$map->reverse($mapping), '/products/edit/2');
    
    $mapping = array(':controller' => 'products', ':action' => 'create');
    $this->assert_equal("build route", (string)$map->reverse($mapping), '/products/create');
    
    $mapping = array(':controller' => 'products');
    $this->assert_equal("build route", (string)$map->reverse($mapping), '/products');
    
    $mapping = array(':controller' => 'products', ':format' => 'html');
    $this->assert_equal("build route", (string)$map->reverse($mapping), '/products.html');
    
    $mapping = array(':controller' => 'products', ':action' => 'create', ':format' => 'html');
    $this->assert_equal("build route", (string)$map->reverse($mapping), '/products/create.html');
    
    $mapping = array(':controller' => 'products', ':action' => 'edit', ':id' => 10, ':format' => 'xml');
    $this->assert_equal("build route", (string)$map->reverse($mapping), '/products/edit/10.xml');
    
    $mapping = array(':controller' => 'pages', ':action' => 'show', ':id' => 'toto', ':format' => 'json');
    $this->assert_equal("build route", (string)$map->reverse($mapping), '/page/toto.json');
    
    $mapping = array(':controller' => 'posts', ':action' => 'delete', ':id' => 45);
    $test = $map->reverse($mapping);
    $this->assert_equal("build route", $test->path, '/posts/45');
    $this->assert_equal("build route", $test->method, 'DELETE');
  }
  
  function test_path_for()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->connect(':controller/:action/:id.:format');
    
    $options = array(':controller' => 'pages', ':action' => 'show', ':id' => 'toto', ':format' => 'json');
    $this->assert_equal("default route", url_for($options), '/pages/show/toto.json');
    
    $options = array(':controller' => 'pages', ':format' => 'xml', 'order' => 'asc');
    $this->assert_equal("with query string", url_for($options), '/pages.xml?order=asc');
    
    $options = array(':controller' => 'pages', ':format' => 'xml', 'order' => 'asc', 'path_only' => false);
    $this->assert_equal("with query string", url_for($options), 'http://localhost:3009/pages.xml?order=asc');
  }
  
  function test_named_routes()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->named('about',    'about', array(':controller' => 'html', ':action' => 'about'));
    $map->named('purchase', 'products/:id/purchase', array(':controller' => 'catalog', ':action' => 'purchase'));
    $map->build_path_and_url_helpers();
    
    $this->assert_equal('/about', $map->route('GET', 'about'), array(
      ':method'     => 'GET',
      ':controller' => 'html',
      ':action'     => 'about',
      ':format'     => null,
    ));
    $this->assert_true("about_path()", function_exists('about_path'));
    $this->assert_true("about_url()",  function_exists('about_url'));
    
    $this->assert_equal('/products/45/purchase', $map->route('GET', 'products/45/purchase'), array(
      ':method'     => 'GET',
      ':controller' => 'catalog',
      ':action'     => 'purchase',
      ':id'         => '45',
      ':format'     => null,
    ));
    $this->assert_true("purchase_path()", function_exists('purchase_path'));
    $this->assert_true("purchase_url()", function_exists('purchase_url'));
  }
  
  function test_named_resource_path()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->resource('users');
    $map->build_path_and_url_helpers();
    
    $this->assert_true('resources_path()',       function_exists('users_path'));
    $this->assert_true('show_resource_path()',   function_exists('show_user_path'));
    $this->assert_true('new_resource_path()',    function_exists('new_user_path'));
    $this->assert_true('create_resource_path()', function_exists('create_user_path'));
    $this->assert_true('edit_resource_path()',   function_exists('edit_user_path'));
    $this->assert_true('update_resource_path()', function_exists('update_user_path'));
    $this->assert_true('delete_resource_path()', function_exists('delete_user_path'));
    
    $expected = new ActionController_Path('GET', 'users');
    $this->assert_equal('GET /users', users_path(), $expected);
    $expected = new ActionController_Url('GET', 'users');
    $this->assert_equal('GET /users', users_url(), $expected);
    
    $expected = new ActionController_Path('GET', 'users/1');
    $this->assert_equal('GET /users/1', show_user_path(array(':id' => 1)), $expected);
    $expected = new ActionController_Url('GET', 'users/1');
    $this->assert_equal('GET /users/1', show_user_url(array(':id' => 1)), $expected);
    
    $expected = new ActionController_Path('GET', 'users/new');
    $this->assert_equal('GET /users/new', new_user_path(), $expected);
    $expected = new ActionController_Url('GET', 'users/new');
    $this->assert_equal('GET /users/new', new_user_url(), $expected);
    
    $expected = new ActionController_Path('GET', 'users/1/edit');
    $this->assert_equal('GET /users/1/edit', edit_user_path(array(':id' => 1)), $expected);
    $expected = new ActionController_Url('GET', 'users/1/edit');
    $this->assert_equal('GET /users/1/edit', edit_user_url(array(':id' => 1)), $expected);
    
    $expected = new ActionController_Path('POST', 'users');
    $this->assert_equal('POST /users', create_user_path(), $expected);
    $expected = new ActionController_Url('POST', 'users');
    $this->assert_equal('POST /users', create_user_url(), $expected);
    
    $expected = new ActionController_Path('PUT', 'users/1');
    $this->assert_equal('PUT /users/1', update_user_path(array(':id' => 1)), $expected);
    $expected = new ActionController_Url('PUT', 'users/1');
    $this->assert_equal('PUT /users/1', update_user_url(array(':id' => 1)), $expected);
    
    $expected = new ActionController_Path('DELETE', 'users/1');
    $this->assert_equal('DELETE /users/1', delete_user_path(array(':id' => 1)), $expected);
    $expected = new ActionController_Url('DELETE', 'users/1');
    $this->assert_equal('DELETE /users/1', delete_user_url(array(':id' => 1)), $expected);
    
    $expected = new ActionController_Path('GET', 'users/45/edit');
    $this->assert_equal('edit_user_path(:id)', edit_user_path(45), $expected);
    $expected = new ActionController_url('GET', 'users/45/edit');
    $this->assert_equal('edit_user_url(:id)', edit_user_url(45), $expected);
    
    $expected = new ActionController_Path('GET', 'users/72');
    $this->assert_equal('show_user_path(:id)', show_user_path(72), $expected);
    $expected = new ActionController_Url('GET', 'users/72');
    $this->assert_equal('show_user_url(:id)', show_user_url(72), $expected);
  }
  
  function test_named_root_path()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->root(array(':controller' => 'welcome'));
    $map->build_path_and_url_helpers();
    
    $this->assert_true('root_path() exists', function_exists('root_path'));
    $this->assert_true('root_url() exists',  function_exists('root_url'));
    
    $this->assert_equal('root_path', (string)root_path(), '/');
    $this->assert_equal('root_url', (string)root_url(), 'http://localhost:3009/');
  }
  
  function test_named_routes_with_activerecord()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->resource('products');
    $map->build_path_and_url_helpers();
    $this->fixtures('products');
    
    $this->assert_equal('', (string)show_product_path(new Product(1)), '/products/1');
    $this->assert_equal('', (string)edit_product_path(new Product(3)), '/products/3/edit');
    
    $this->assert_equal('', (string)show_product_url(new Product(2)), 'http://localhost:3009/products/2');
    $this->assert_equal('', (string)edit_product_url(new Product(1)), 'http://localhost:3009/products/1/edit');
  }
  
  function test_url_for_activerecord()
  {
    $this->assert_equal('', (string)url_for(new Product(2)), 'http://localhost:3009/products/2');
    $this->assert_equal('', (string)url_for(new Product(3)), 'http://localhost:3009/products/3');
  }
  
  function test_named_routes_with_current_request_format()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->resource('articles');
    $map->connect(':controller/:action/:id.:format');
    $map->build_path_and_url_helpers();
    
    $map->route('GET', '/articles/create.xml');
    $this->assert_equal('', (string)show_article_path(4), '/articles/4.xml');
    
    $map->route('PUT', '/articles/update.html');
    $this->assert_equal('', (string)show_article_path(3), '/articles/3.html');
    
    $map->route('GET', '/articles/5/edit');
    $this->assert_equal('', (string)show_article_path(5), '/articles/5');
  }
  
  function test_named_routes_with_query_string()
  {
    $this->assert_equal('', (string)show_article_path(array(':id' => 1, 'session_id' => 'abcd')),
      '/articles/1?session_id=abcd');
    $this->assert_equal('', (string)update_article_path(array(':id' => 43, 'edit' => '1', 'action' => 'first')),
      '/articles/43?edit=1&action=first');
  }
}

new Test_ActionController_Routing();

?>
