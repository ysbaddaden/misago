<?php

$_ENV['MISAGO_ENV'] = 'test';
$location = dirname(__FILE__).'/../../..';

require_once "$location/test/test_app/config/boot.php";

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
    $mapping = array(':controller' => 'pages', ':action' => 'show', ':id' => 'toto', ':format' => 'json');
    $this->assert_equal("build route", path_for($mapping), '/page/toto.json');
  }
  
  function test_build_url_and_path_helpers()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->connect('product/:id.:format', array(':controller' => 'products', ':action' => 'show'));
    $map->connect(':controller/:action/:id.:format');
    $map->build_path_and_url_helpers();
    
    $this->assert_true('show_product_path()', function_exists('show_product_path'));
    $this->assert_true('products_path()',     function_exists('products_path'));
    
    $this->assert_equal('/products',    (string)products_path(), '/products/index');
    $this->assert_equal('/product/123', (string)show_product_path(array(':id' => 123)), '/product/123');
    $this->assert_equal('/product/123', (string)show_product_path(array(':id' => 123, ':format' => 'html')), '/product/123.html');
    
    $this->assert_equal('/say/hello_who/Julien', (string)hello_who_say_path(array(':id' => 'Julien')), '/say/hello_who/Julien');
    $this->assert_equal('/say/hello_who/Julien', (string)hello_who_say_path(array(':id' => 'Julien', ':format' => 'xml')), '/say/hello_who/Julien.xml');
    
#    $this->assert_true('show_product_url()',  function_exists('show_product_url'));
#    $this->assert_equal('/say/hello_who/Julien', hello_who_say_url(array(':id' => 'Julien')), '/say/hello_who/Julien');

    $map->reset();
    $map->resource('users');
    $map->build_path_and_url_helpers();
    
    $expected = new ActionController_Path('GET', 'users');
    $this->assert_equal('GET /users', users_path(array(':id' => 1)), $expected);
    
    $expected = new ActionController_Path('GET', 'users/1');
    $this->assert_equal('GET /users/1', show_user_path(array(':id' => 1)), $expected);
    
    $expected = new ActionController_Path('GET', 'users/new');
    $this->assert_equal('GET /users/new', new_user_path(), $expected);
    
    $expected = new ActionController_Path('GET', 'users/1/edit');
    $this->assert_equal('GET /users/1/edit', edit_user_path(array(':id' => 1)), $expected);
    
    $expected = new ActionController_Path('POST', 'users');
    $this->assert_equal('POST /users', create_user_path(), $expected);
    
    $expected = new ActionController_Path('PUT', 'users/1');
    $this->assert_equal('PUT /users/1', update_user_path(array(':id' => 1)), $expected);
    
    $expected = new ActionController_Path('DELETE', 'users/1');
    $this->assert_equal('DELETE /users/1', delete_user_path(array(':id' => 1)), $expected);
  }
}

new Test_ActionController_Routing();

?>
