<?php
namespace Misago\ActionController\Routing;
use Misago\ActiveSupport\String;

# =RESTful routes
# 
# REST webservices are handled transparently by misago.
# 
# Attention: in RESTful routes +:id+ must be an integer.
# 
# Example:
# 
#   $map->resource('posts');
# 
# This will create the following named routes:
# 
#   GET    /posts          => PostsController::index()
#   GET    /posts/new      => PostsController::neo()
#   POST   /posts          => PostsController::create()
#   GET    /posts/:id/edit => PostsController::edit()
#   GET    /posts/:id      => PostsController::show()
#   PUT    /posts/:id      => PostsController::update()
#   DELETE /posts/:id      => PostsController::delete()
# 
# Of course being named routes it also creates the following helper functions
# (they also exists with the +_url+ form):
# 
#   posts_path()       => GET    /posts
#   new_post_path()    => GET    /posts/new
#   create_post_path() => POST   /posts
#   show_post_path()   => GET    /posts/:id
#   edit_post_path()   => GET    /posts/:id/edit
#   update_post_path() => PUT    /posts/:id
#   delete_post_path() => DELETE /posts/:id
# 
# To create a REST resource, just generate it:
#
#   $ script/generate resource posts
# 
# This will create the controller, the model and add the route to your
# configuration.
# 
class Resources extends \Misago\Object
{
  # Builds RESTful connections.
  function resource($name, $options=array())
  {
    $plural   = $name;
    $singular = String::singularize($name);
    $this->generate_resource($name, $plural, $singular, $name);
    
    if (isset($options['has_many']))
    {
      $prefix = "$name/:{$singular}_id";
      foreach(array_collection($options['has_many']) as $nested_name)
      {
        $nested_plural   = "{$singular}_$nested_name";
        $nested_singular = $singular.'_'.String::singularize($nested_name);
        $this->generate_resource($nested_name, $nested_plural, $nested_singular,
          "$prefix/$nested_name");
      }
    }
  }
  
  private function generate_resource($name, $plural, $singular, $prefix)
  {
    $this->named("$plural",          "$prefix.:format",          array(':controller' => $name, ':action' => 'index',  'conditions' => array('method' => 'GET')));
    $this->named("new_$singular",    "$prefix/new.:format",      array(':controller' => $name, ':action' => 'neo',    'conditions' => array('method' => 'GET')));
    $this->named("show_$singular",   "$prefix/:id.:format",      array(':controller' => $name, ':action' => 'show',   'conditions' => array('method' => 'GET'),    'requirements' => array(':id' => '\d+')));
    $this->named("edit_$singular",   "$prefix/:id/edit.:format", array(':controller' => $name, ':action' => 'edit',   'conditions' => array('method' => 'GET'),    'requirements' => array(':id' => '\d+')));
    $this->named("create_$singular", "$prefix.:format",          array(':controller' => $name, ':action' => 'create', 'conditions' => array('method' => 'POST')));
    $this->named("update_$singular", "$prefix/:id.:format",      array(':controller' => $name, ':action' => 'update', 'conditions' => array('method' => 'PUT'),    'requirements' => array(':id' => '\d+')));
    $this->named("delete_$singular", "$prefix/:id.:format",      array(':controller' => $name, ':action' => 'delete', 'conditions' => array('method' => 'DELETE'), 'requirements' => array(':id' => '\d+')));
  }
}

?>
