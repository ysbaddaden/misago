<?php
require_once __DIR__.'/../../../unit.php';
use Misago\ActionController;

class Test_ActionController_Routing_Routes extends Misago\Unit\TestCase
{
  protected $fixtures = array('products');
  
  function test_draw()
  {
    $map = ActionController\Routing\Routes::draw();
    $this->assert_equal(get_class($map), 'Misago\ActionController\Routing\Routes');
  }
  
  function test_map_root()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_equal($map->route('GET', ''), array(
      ':method'     => 'GET',
      ':controller' => 'welcome',
      ':action'     => 'home',
      ':format'     => null,
    ));
  }
  
  function test_map_default_connect()
  {
    $map = ActionController\Routing\Routes::draw();
    
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
    
    $this->assert_equal($map->route('GET', 'blog/1'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'blog/1.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal($map->route('POST', 'blog/1/create'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'create',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('POST', 'blog/1/edit.xml'), array(
      ':method'     => 'POST',
      ':controller' => 'posts',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => 'xml',
    ));
    
    $this->assert_equal($map->route('GET', 'blog.xml'), array(
      ':method'     => 'GET',
      ':controller' => 'posts',
      ':action'     => 'index',
      ':format'     => 'xml',
    ));
  }
  
  function test_route_globbing()
  {
    $map = ActionController\Routing\Routes::draw();

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
    
    $mapping = array(':controller' => 'photos', ':action' => 'publish', ':id' => 4);
    $this->assert_equal((string)$map->reverse($mapping), '/photos/publish/4');
    
    $mapping = array(':id' => 5, ':controller' => 'photos', ':action' => 'publish');
    $this->assert_equal((string)$map->reverse($mapping), '/photos/publish/5');
    
    $mapping = array(':controller' => 'photos', ':action' => 'publish', ':id' => 4, ':format' => 'xml');
    $this->assert_equal((string)$map->reverse($mapping), '/photos/publish/4.xml');
    
    $mapping = array(':controller' => 'photos', ':action' => 'latest');
    $this->assert_equal((string)$map->reverse($mapping), '/photos/latest');
    
    $mapping = array(':format' => 'rss', ':controller' => 'photos', ':action' => 'latest');
    $this->assert_equal((string)$map->reverse($mapping), '/photos/latest.rss');
    
    $mapping = array(':controller' => 'photos');
    $this->assert_equal((string)$map->reverse($mapping), '/photos');
    
    $mapping = array(':controller' => 'photos', ':action' => 'index');
    $this->assert_equal((string)$map->reverse($mapping), '/photos');
    
    $mapping = array(':controller' => 'threads', ':action' => 'show', ':id' => 'toto');
    $this->assert_equal((string)$map->reverse($mapping), '/thread/toto');
    
    $mapping = array(':controller' => 'products', ':action' => 'edit', ':id' => 2);
    $this->assert_equal((string)$map->reverse($mapping), '/products/2/edit');
    
    $mapping = array(':controller' => 'products', ':action' => 'create');
    $this->assert_equal((string)$map->reverse($mapping), '/products');
    
    $mapping = array(':controller' => 'products');
    $this->assert_equal((string)$map->reverse($mapping), '/products');
    
    $mapping = array(':controller' => 'products', ':format' => 'html');
    $this->assert_equal((string)$map->reverse($mapping), '/products.html');
    
    $mapping = array(':controller' => 'products', ':action' => 'create', ':format' => 'html');
    $this->assert_equal((string)$map->reverse($mapping), '/products.html');
    
    $mapping = array(':controller' => 'products', ':action' => 'edit', ':id' => 10, ':format' => 'xml');
    $this->assert_equal((string)$map->reverse($mapping), '/products/10/edit.xml');
    
    $mapping = array(':controller' => 'threads', ':action' => 'show', ':id' => 'toto', ':format' => 'json');
    $this->assert_equal((string)$map->reverse($mapping), '/thread/toto.json');
    
    $mapping = array(':controller' => 'posts', ':action' => 'delete', ':id' => 45);
    $test = $map->reverse($mapping);
    $this->assert_equal($test->path, '/blog/45/delete');
    $this->assert_equal($test->method, 'GET');
    
    $mapping = array(':controller' => 'products', ':action' => 'update', ':id' => 45);
    $test = $map->reverse($mapping);
    $this->assert_equal($test->path, '/products/45');
    $this->assert_equal($test->method, 'PUT');
  }
  
  function test_named_routes()
  {
    $map = ActionController\Routing\Routes::draw();
    
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
  
#  function test_namespace()
#  {
#    $map = ActionController\Routing\Routes::draw();
#    
#    $controller = \Misago\ActionController\Routing\Routes::recognize(
#      new \Misago\ActionController\TestRequest(array(
#        'path' => 'admin/products/2/edit',
#      ))
#    );
#    $this->assert_instance_of($controller, '\Admin\ProductsController');
#    
#    $this->assert_true(function_exists('admin_products_path'));
#    $this->assert_equal((string)admin_products_path(),      '/admin/products');
#    $this->assert_equal((string)new_admin_product_path(),   '/admin/products/new');
#    $this->assert_equal((string)edit_admin_product_path(1), '/admin/products/1/edit');
#    
#    $this->assert_equal($map->route('GET', 'admin/products/456/edit.xml'), array(
#      ':method' => 'GET', ':controller' => 'admin\products', ':action' => 'edit', ':id' => '456', ':format' => 'xml'));
#    $this->assert_equal($map->route('GET', 'admin/products'), array(
#      ':method' => 'GET', ':controller' => 'admin\products', ':action' => 'index', ':format' => null));
#  }
  
  function test_named_root_path()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_true(function_exists('root_path'));
    $this->assert_true(function_exists('root_url'));
    
    $this->assert_equal((string)root_path(), '/');
    $this->assert_equal((string)root_url(), 'http://localhost:3009/');
  }
  
  function test_named_routes_with_activerecord()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_equal((string)product_path(new Product(1)), '/products/1');
    $this->assert_equal((string)edit_product_path(new Product(3)), '/products/3/edit');
    
    $this->assert_equal((string)product_url(new Product(2)), 'http://localhost:3009/products/2');
    $this->assert_equal((string)edit_product_url(new Product(1)), 'http://localhost:3009/products/1/edit');
  }
  
  function test_named_routes_with_query_string()
  {
    $this->assert_equal((string)article_path(array(':id' => 1, 'session_id' => 'abcd')),
      '/articles/1?session_id=abcd');
    $this->assert_equal((string)article_path(array(':id' => 43, 'edit' => '1', 'action' => 'first')),
      '/articles/43?action=first&edit=1');
    
    # using query string parameters (string) sharing the name of path parameters (symbol)
    $this->assert_equal((string)article_path(array(':id' => 2, 'format' => 'html')), '/articles/2?format=html');
    $this->assert_equal((string)article_path(array('id' => 2)), '/articles/:id?id=2');
  }
}

?>
