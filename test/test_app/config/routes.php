<?php
#
# Priority is based upon order of creation.
#
$map = Misago\ActionController\Routing\Routes::draw();

# regular routes
$map->connect('blog.:format', array(':controller' => 'posts', ':action' => 'index'));
$map->connect('blog/:id.:format', array(':controller' => 'posts', ':action' => 'show'));
$map->connect('blog/:id/:action', array(':controller' => 'posts'));
$map->connect('blog/:id/:action.:format', array(':controller' => 'posts'));

$map->connect('thread/:id', array(':controller' => 'threads', ':action' => 'show'));
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
#$map->resources('posts');
$map->resources('products');
$map->resource('geocoder', array(
  'member'     => array('add' => 'get', 'remove' => 'delete'),
  'collection' => array('all' => 'get')
));
$map->resources('wiki');
#$map->resources('users');
$map->resources('accounts');
$map->resources('articles');

# nested restful resource(s)
$map->resources('discussions', array('has_many' => 'messages', 'has_one' => 'author'));
$map->resources('events', array(
  'member'     => array('publish'  => 'put', 'tags'   => 'get'),
  'collection' => array('archives' => 'get', 'latest' => 'get')),
  function($event)
{
  $event->resource('description', array('as' => 'about'));
  $event->resources('tickets');
});

#$map->resources('categories', array('as' => 'kategorien'));

# namespaced resource(s)
#$map->ns('admin', function($admin) {
#  $admin->resources('products');
#});

# landing page
$map->root(array(':controller' => 'welcome', ':action' => 'home'));

# default routes
$map->connect(':controller/:action/:id');
$map->connect(':controller/:action/:id.:format');
?>
