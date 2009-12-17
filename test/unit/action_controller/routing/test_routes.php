<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../../test/test_app/config/boot.php';
use Misago\ActionController;

class Test_ActionController_Routing extends Misago\Unit\TestCase
{
  function test_draw()
  {
    $map = ActionController\Routing\Routes::draw();
    $this->assert_equal(get_class($map), 'Misago\ActionController\Routing\Routes');
  }
  
  function test_map_root()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    
    $map->root(array(':controller' => 'welcome', ':action' => 'home'));
    $this->assert_equal($map->route('GET', ''), array(
      ':method'     => 'GET',
      ':controller' => 'welcome',
      ':action'     => 'home',
      ':format'     => null,
    ));
    
    $map->root(array(':controller' => 'welcome', ':action' => 'home', ':format' => 'xml'));
    $this->assert_equal($map->route('GET', ''), array(
      ':method'     => 'GET',
      ':controller' => 'welcome',
      ':action'     => 'home',
      ':format'     => 'xml',
    ));
  }
  
  function test_map_default_connect()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    
    $map->connect(':controller/:action/:id.:format');
    
    $this->assert_equal($map->route('GET', 'posts/show/1'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('POST', 'posts/edit/1.xml'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal($map->route('GET', 'posts.json'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => 'json',
    ));
    
    $this->assert_equal($map->route('POST', 'posts.json'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => 'json',
    ));
  }
  
  function test_map_connect()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    
    $map->connect('posts.:format', array(':controller' => 'posts', ':action' => 'index'));
    $map->connect('posts/:id.:format', array(':controller' => 'posts', ':action' => 'show'));
    $map->connect('posts/:id/:action.:format', array(':controller' => 'posts'));
    
    $this->assert_equal($map->route('GET', 'posts/1'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'posts/1.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal($map->route('POST', 'posts/1/create'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'create',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('POST', 'posts/1/edit.xml'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal($map->route('GET', 'posts.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => 'xml',
    ));
  }
  
  function test_map_resources()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    
    $map->resources('posts');
    $map->connect(':controller/:action/:id.:format');
    
    $this->assert_equal($map->route('GET', 'posts'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'posts/1'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'posts/new'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'neo',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'posts/1/edit'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('POST', 'posts'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'create',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('PUT', 'posts/1'), array(
      ':method'     => 'PUT',
      ':controller' => 'posts',
      ':action'     => 'update',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('DELETE', 'posts/1'), array(
      ':method'     => 'DELETE',
      ':controller' => 'posts',
      ':action'     => 'delete',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'posts/widget'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'widget',
      ':format'     => null,
    ));
  }
  
  function test_map_resource()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    
    $map->resource('event');
    $map->connect(':controller/:action/:id.:format');
    
    $this->assert_equal($map->route('GET', 'event'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'index',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'event/1'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'event/new'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'neo',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'event/1/edit'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('POST', 'event'), array(
      ':method'     => 'POST',
      ':controller' => 'events',
      ':action'     => 'create',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('PUT', 'event/1'), array(
      ':method'     => 'PUT',
      ':controller' => 'events',
      ':action'     => 'update',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('DELETE', 'event/1'), array(
      ':method'     => 'DELETE',
      ':controller' => 'events',
      ':action'     => 'delete',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'events/widget'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'widget',
      ':format'     => null,
    ));
  }
  
  function test_route_globbing()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->connect('help/*path.:format', array(':controller' => 'html_pages', ':action' => 'help'));

    $this->assert_equal($map->route('GET', 'help/webcomics/pages/create'), array(
      ':method'     => 'GET',
      ':controller' => 'html_pages',
      ':action'     => 'help',
      ':path'       => 'webcomics/pages/create',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'help/webcomics/pages/create.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'html_pages',
      ':action'     => 'help',
      ':path'       => 'webcomics/pages/create',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal($map->route('GET', 'help/webcomics/pages.old/create.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'html_pages',
      ':action'     => 'help',
      ':path'       => 'webcomics/pages.old/create',
      ':format'     => 'xml',
    ));
  }
  
  function test_route_requirements()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->connect('page/:id.:format', array(':controller' => 'pages', ':action' => 'show',
      'requirements' => array(':id' => '\d+')));
    
    $this->assert_equal($map->route('GET', 'page/456'), array(
      ':method'     => 'GET',
      ':controller' => 'pages',
      ':action'     => 'show',
      ':id'         => '456',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'page/456.xml'), array(
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
      $this->assert_true(true);
    }
  }
  
  function test_reverse()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->resources('posts');
    $map->connect('page/:id.:format', array(':controller' => 'pages', ':action' => 'show'));
    $map->connect(':controller/:action/:id.:format');
    
    $mapping = array(':controller' => 'pages', ':action' => 'show', ':id' => 'toto');
    $this->assert_equal((string)$map->reverse($mapping), '/page/toto');
    
    $mapping = array(':controller' => 'products', ':action' => 'edit', ':id' => 2);
    $this->assert_equal((string)$map->reverse($mapping), '/products/edit/2');
    
    $mapping = array(':controller' => 'products', ':action' => 'create');
    $this->assert_equal((string)$map->reverse($mapping), '/products/create');
    
    $mapping = array(':controller' => 'products');
    $this->assert_equal((string)$map->reverse($mapping), '/products');
    
    $mapping = array(':controller' => 'products', ':format' => 'html');
    $this->assert_equal((string)$map->reverse($mapping), '/products.html');
    
    $mapping = array(':controller' => 'products', ':action' => 'create', ':format' => 'html');
    $this->assert_equal((string)$map->reverse($mapping), '/products/create.html');
    
    $mapping = array(':controller' => 'products', ':action' => 'edit', ':id' => 10, ':format' => 'xml');
    $this->assert_equal((string)$map->reverse($mapping), '/products/edit/10.xml');
    
    $mapping = array(':controller' => 'pages', ':action' => 'show', ':id' => 'toto', ':format' => 'json');
    $this->assert_equal((string)$map->reverse($mapping), '/page/toto.json');
    
    $mapping = array(':controller' => 'posts', ':action' => 'delete', ':id' => 45);
    $test = $map->reverse($mapping);
    $this->assert_equal($test->path, '/posts/45');
    $this->assert_equal($test->method, 'DELETE');
  }
  
  function test_url_for()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->connect(':controller/:action/:id.:format');
    
    $options = array(':controller' => 'pages', ':action' => 'show', ':id' => 'toto', ':format' => 'json');
    $this->assert_equal(url_for($options), '/pages/show/toto.json');
    
    $options = array(':controller' => 'pages', ':format' => 'xml', 'order' => 'asc');
    $this->assert_equal(url_for($options), '/pages.xml?order=asc');
    
    $options = array(':controller' => 'pages', ':format' => 'xml', 'order' => 'asc', 'path_only' => false);
    $this->assert_equal(url_for($options), 'http://localhost:3009/pages.xml?order=asc');
    
    $this->assert_equal(url_for(array(':controller' => 'admin', 'format' => 'html')), '/admin?format=html',
      "using query string parameter (string) sharing path parameter (symbol) name");
  }
  
  function test_named_routes()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->named('about',    'about', array(':controller' => 'html', ':action' => 'about'));
    $map->named('purchase', 'products/:id/purchase', array(':controller' => 'catalog', ':action' => 'purchase'));
    $map->build_named_route_helpers();
    
    $this->assert_equal($map->route('GET', 'about'), array(
      ':method'     => 'GET',
      ':controller' => 'html',
      ':action'     => 'about',
      ':format'     => null,
    ));
    $this->assert_true(function_exists('about_path'));
    $this->assert_true(function_exists('about_url'));
    
    $this->assert_equal($map->route('GET', 'products/45/purchase'), array(
      ':method'     => 'GET',
      ':controller' => 'catalog',
      ':action'     => 'purchase',
      ':id'         => '45',
      ':format'     => null,
    ));
    $this->assert_true(function_exists('purchase_path'));
    $this->assert_true(function_exists('purchase_url'));
  }
  
  function test_named_routes_for_resources()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->resources('users');
    $map->build_named_route_helpers();
    
    $this->assert_true(function_exists('users_path'));
    $this->assert_true(function_exists('show_user_path'));
    $this->assert_true(function_exists('new_user_path'));
    $this->assert_true(function_exists('create_user_path'));
    $this->assert_true(function_exists('edit_user_path'));
    $this->assert_true(function_exists('update_user_path'));
    $this->assert_true(function_exists('delete_user_path'));
    
    $this->assert_equal(users_path(), new ActionController\Routing\Path('GET', 'users'));
    $this->assert_equal(users_url(),  new ActionController\Routing\Url('GET', 'users'));
    
    $this->assert_equal(show_user_path(array(':id' => 1)), new ActionController\Routing\Path('GET', 'users/1'));
    $this->assert_equal(show_user_url(array(':id' => 1)),  new ActionController\Routing\Url('GET', 'users/1'));
    
    $this->assert_equal(new_user_path(), new ActionController\Routing\Path('GET', 'users/new'));
    $this->assert_equal(new_user_url(),  new ActionController\Routing\Url('GET', 'users/new'));
    
    $this->assert_equal(edit_user_path(array(':id' => 1)), new ActionController\Routing\Path('GET', 'users/1/edit'));
    $this->assert_equal(edit_user_url(array(':id' => 1)),  new ActionController\Routing\Url('GET', 'users/1/edit'));
    
    $this->assert_equal(create_user_path(), new ActionController\Routing\Path('POST', 'users'));
    $this->assert_equal(create_user_url(),  new ActionController\Routing\Url('POST', 'users'));
    
    $this->assert_equal(update_user_path(array(':id' => 1)), new ActionController\Routing\Path('PUT', 'users/1'));
    $this->assert_equal(update_user_url(array(':id' => 1)),  new ActionController\Routing\Url('PUT', 'users/1'));
    
    $this->assert_equal(delete_user_path(array(':id' => 1)), new ActionController\Routing\Path('DELETE', 'users/1'));
    $this->assert_equal(delete_user_url(array(':id' => 1)),  new ActionController\Routing\Url('DELETE', 'users/1'));
    
    $this->assert_equal(edit_user_path(45), new ActionController\Routing\Path('GET', 'users/45/edit'));
    $this->assert_equal(edit_user_url(45),  new ActionController\Routing\Url('GET', 'users/45/edit'));
    
    $this->assert_equal(show_user_path(72), new ActionController\Routing\Path('GET', 'users/72'));
    $this->assert_equal(show_user_url(72),  new ActionController\Routing\Url('GET', 'users/72'));
  }
  
  function test_named_routes_for_resource()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->resource('account');
    $map->build_named_route_helpers();
    
    $this->assert_true(function_exists('account_path'));
    $this->assert_true(function_exists('show_account_path'));
    $this->assert_true(function_exists('new_account_path'));
    $this->assert_true(function_exists('create_account_path'));
    $this->assert_true(function_exists('edit_account_path'));
    $this->assert_true(function_exists('update_account_path'));
    $this->assert_true(function_exists('delete_account_path'));
    
    $this->assert_equal(account_path(), new ActionController\Routing\Path('GET', 'account'));
    $this->assert_equal(account_url(),  new ActionController\Routing\Url('GET', 'account'));
    
    $this->assert_equal(show_account_path(array(':id' => 1)), new ActionController\Routing\Path('GET', 'account/1'));
    $this->assert_equal(show_account_url(array(':id' => 1)),  new ActionController\Routing\Url('GET', 'account/1'));
    
    $this->assert_equal(new_account_path(), new ActionController\Routing\Path('GET', 'account/new'));
    $this->assert_equal(new_account_url(),  new ActionController\Routing\Url('GET', 'account/new'));
    
    $this->assert_equal(edit_account_path(array(':id' => 1)), new ActionController\Routing\Path('GET', 'account/1/edit'));
    $this->assert_equal(edit_account_url(array(':id' => 1)),  new ActionController\Routing\Url('GET', 'account/1/edit'));
    
    $this->assert_equal(create_account_path(), new ActionController\Routing\Path('POST', 'account'));
    $this->assert_equal(create_account_url(),  new ActionController\Routing\Url('POST', 'account'));
    
    $this->assert_equal(update_account_path(array(':id' => 1)), new ActionController\Routing\Path('PUT', 'account/1'));
    $this->assert_equal(update_account_url(array(':id' => 1)),  new ActionController\Routing\Url('PUT', 'account/1'));
    
    $this->assert_equal(delete_account_path(array(':id' => 1)), new ActionController\Routing\Path('DELETE', 'account/1'));
    $this->assert_equal(delete_account_url(array(':id' => 1)),  new ActionController\Routing\Url('DELETE', 'account/1'));
    
    $this->assert_equal(edit_account_path(45), new ActionController\Routing\Path('GET', 'account/45/edit'));
    $this->assert_equal(edit_account_url(45),  new ActionController\Routing\Url('GET', 'account/45/edit'));
    
    $this->assert_equal(show_account_path(72), new ActionController\Routing\Path('GET', 'account/72'));
    $this->assert_equal(show_account_url(72),  new ActionController\Routing\Url('GET', 'account/72'));
  }
  
  function test_nested_resources()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    
    $map->resources('discussions', array('has_many' => 'messages'));
    $map->build_named_route_helpers();
    
    $this->assert_equal((string)discussions_path(), '/discussions');
    
    $this->assert_equal(discussion_messages_path(array(':discussion_id' => 34)), new ActionController\Routing\Path('GET', 'discussions/34/messages'));
    $this->assert_equal(new_discussion_message_path(array(':discussion_id' => 43)), new ActionController\Routing\Path('GET', 'discussions/43/messages/new'));
    $this->assert_equal(create_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)), new ActionController\Routing\Path('POST', 'discussions/13/messages'));
    $this->assert_equal(show_discussion_message_path(array(':discussion_id' => 46, ':id' => 12)), new ActionController\Routing\Path('GET', 'discussions/46/messages/12'));
    $this->assert_equal(edit_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)), new ActionController\Routing\Path('GET', 'discussions/13/messages/26/edit'));
    $this->assert_equal(update_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)), new ActionController\Routing\Path('PUT', 'discussions/13/messages/26'));
    $this->assert_equal(delete_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)), new ActionController\Routing\Path('DELETE', 'discussions/13/messages/26'));
  }
  
  function test_named_root_path()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->root(array(':controller' => 'welcome'));
    $map->build_named_route_helpers();
    
    $this->assert_true(function_exists('root_path'));
    $this->assert_true(function_exists('root_url'));
    
    $this->assert_equal((string)root_path(), '/');
    $this->assert_equal((string)root_url(), 'http://localhost:3009/');
  }
  
  function test_named_routes_with_activerecord()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->resources('products');
    $map->build_named_route_helpers();
    $this->fixtures('products');
    
    $this->assert_equal((string)show_product_path(new Product(1)), '/products/1');
    $this->assert_equal((string)edit_product_path(new Product(3)), '/products/3/edit');
    
    $this->assert_equal((string)show_product_url(new Product(2)), 'http://localhost:3009/products/2');
    $this->assert_equal((string)edit_product_url(new Product(1)), 'http://localhost:3009/products/1/edit');
  }
  
  function test_url_for_activerecord()
  {
    $this->assert_equal((string)url_for(new Product(2)), 'http://localhost:3009/products/2');
    $this->assert_equal((string)url_for(new Product(3)), 'http://localhost:3009/products/3');
  }
  
  function test_named_routes_with_current_request_format()
  {
    $map = ActionController\Routing\Routes::draw();
    $map->reset();
    $map->resources('articles');
    $map->connect(':controller/:action/:id.:format');
    $map->build_named_route_helpers();
    
    $map->route('GET', '/articles/create.xml');
    $this->assert_equal((string)show_article_path(4), '/articles/4.xml');
    
    $map->route('PUT', '/articles/update.html');
    $this->assert_equal((string)show_article_path(3), '/articles/3.html');
    
    $map->route('GET', '/articles/5/edit');
    $this->assert_equal((string)show_article_path(5), '/articles/5');
  }
  
  function test_named_routes_with_query_string()
  {
    $this->assert_equal((string)show_article_path(array(':id' => 1, 'session_id' => 'abcd')),
      '/articles/1?session_id=abcd');
    $this->assert_equal((string)update_article_path(array(':id' => 43, 'edit' => '1', 'action' => 'first')),
      '/articles/43?edit=1&action=first');
    
    # using query string parameters (string) sharing the name of path parameters (symbol)
    $this->assert_equal((string)update_article_path(array(':id' => 2, 'format' => 'html')), '/articles/2?format=html');
    $this->assert_equal((string)update_article_path(array('id' => 2)), '/articles/:id?id=2');
  }
}

new Test_ActionController_Routing();

?>
