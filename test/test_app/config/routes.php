<?php
#
# Priority is based upon order of creation.
#
$map = Misago\ActionController\Routing\Routes::draw();

# regular routes
$map->connect('blog.:format', array(':controller' => 'posts', ':action' => 'index'));
$map->connect('blog/:id.:format', array(':controller' => 'posts', ':action' => 'show'));
$map->connect('blog/:id/:action.:format', array(':controller' => 'posts'));

$map->connect('thread/:id.:format', array(':controller' => 'threads', ':action' => 'show'));

# routes with globbing (*path)
$map->connect('help/*path.:format', array(':controller' => 'html_pages', ':action' => 'help'));

# routes with requirements
$map->connect('page/:id.:format', array(':controller' => 'pages', ':action' => 'show',
  'requirements' => array(':id' => '\d+')));

# named routes
$map->named('about',    'about', array(':controller' => 'html', ':action' => 'about'));
$map->named('purchase', 'products/:id/purchase', array(':controller' => 'catalog', ':action' => 'purchase'));

# RESTful resource(s)
#$map->resource('post');
$map->resource('product'/*, array('member' => array('purchase' => 'GET'))*/);
$map->resource('user');
$map->resource('account');
$map->resource('article');

# nested restful resource(s)
$map->resource('discussion', array('has_many' => 'messages'));
$map->resource('event', function($event)
{
  $event->resource('description', array('as' => 'about'));
  $event->resource('ticket');
});

# namespaced resource(s)
$map->ns('admin', function($admin) {
  $admin->resource('product');
});

# landing page
$map->root(array(':controller' => 'welcome', ':action' => 'home'));

# default routes
$map->connect(':controller/:action/:id.:format');

?>
