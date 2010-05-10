<?php
#
# Priority is based upon order of creation.
#
Misago\ActionController\Routing\Routes::draw(function($map)
{
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
  $map->named('purchase', 'products/:id/purchase', array(
    ':controller' => 'catalog',
    ':action'     => 'purchase'
  ));

  # RESTful resource(s)
  #$map->resources('posts');
  $map->resources('products');
  $map->resource('geocoder', array(
    'member'     => array('add' => 'get', 'remove' => 'delete', 'position' => 'any'),
    'collection' => array('all' => 'get'),
    'plural'     => 'geocoder',
  ));
  $map->resources('wiki');
  #$map->resources('users');
  $map->resources('accounts');
  $map->resources('articles');

  # nested restful resource(s)
  $map->resources('discussions', array(
    'has_many' => 'messages',
    'has_one'  => 'author'
  ));
  $map->resources('events', array(
    'member'     => array('publish'  => 'put', 'tags'   => 'get'),
    'collection' => array('archives' => 'get', 'latest' => 'get')),
    function($event)
  {
    $event->resource('description', array('as' => 'about'));
    $event->resources('tickets');
  });

  $map->resources('categories', array(
    'only'   => 'create,update,delete',
    'except' => 'delete',
    'as'     => 'kategorien'
  ));
  $map->resources('tags', array('only' => 'delete'));
  $map->resource('profile', array(
    'only'   => 'show,update,delete',
    'except' => 'delete',
    'as'     => 'profil'), function($profile)
  {
    $profile->resource('picture', array(
      'controller' => 'profile\images',
      'only'       => 'show'
    ));
  });

  # controller
  $map->resources('pictures', array('controller' => 'images', 'only' => 'index'));

  # namespaced resource(s)
  $map->name_space('admin', function($admin)
  {
    $admin->resources('products', array('only' => array('index', 'show')), function($product)
    {
      $product->resources('invoices', array(
        'only'   => array('index', 'show'),
        'member' => array('validate' => 'put')
      ));
    });
    $admin->resource('options', array(
      'only'   => 'edit,update,delete',
      'plural' => 'options'
    ));
  });

  # landing page
  $map->root(array(':controller' => 'welcome', ':action' => 'home'));

  # default routes
  $map->connect(':controller/:action/:id');
  $map->connect(':controller/:action/:id.:format');
});
?>
