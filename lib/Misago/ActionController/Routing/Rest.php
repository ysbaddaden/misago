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
class Rest extends \Misago\Object
{
  function resource($name, $options=array(), $closure=null)
  {
    if ($options instanceof Closure)
    {
      $closure = $options;
      $options = array();
    }
    
    $options['singular'] = $name;
    if (empty($options['plural']))      $options['plural']      = String::pluralize($name);
    if (empty($options['controller']))  $options['controller']  = $options['plural'];
    $options['prefix'] = $options['singular'];
    
    $this->build_resource($name, $options, $closure);
  }
  
  function resources($name, $options=array(), $closure=null)
  {
    if ($options instanceof Closure)
    {
      $closure = $options;
      $options = array();
    }
    
    $options['plural'] = $name;
    if (empty($options['singular']))    $options['singular']    = String::singularize($name);
    if (empty($options['controller']))  $options['controller']  = $options['plural'];
    $options['prefix'] = $options['plural'];
    
    $this->build_resource($name, $options, $closure);
  }
  
  private function build_resource($name, $options, $closure)
  {
    if (!isset($options['name_prefix'])) {
      $options['name_prefix'] = '';
    }
    
    $prefix = isset($options['path_prefix']) ?
      $options['path_prefix'].'/'.$options['prefix'] : $options['prefix'];
    
    $this->named("{$options['name_prefix']}$name", "$prefix.:format", array(
      ':controller' => $options['controller'], ':action' => 'index',
      'conditions' => array('method' => 'GET')
    ));
    
    $this->named("new_{$options['name_prefix']}{$options['singular']}", "$prefix/new.:format", array(
      ':controller' => $options['controller'], ':action' => 'neo',    
      'conditions' => array('method' => 'GET')
    ));
    
    $this->named("show_{$options['name_prefix']}{$options['singular']}", "$prefix/:id.:format", array(
      ':controller' => $options['controller'], ':action' => 'show',   
      'conditions' => array('method' => 'GET'), 'requirements' => array(':id' => '\d+')
    ));
    
    $this->named("edit_{$options['name_prefix']}{$options['singular']}", "$prefix/:id/edit.:format", array(
      ':controller' => $options['controller'], ':action' => 'edit',   
      'conditions' => array('method' => 'GET'), 'requirements' => array(':id' => '\d+')
    ));
    
    $this->named("create_{$options['name_prefix']}{$options['singular']}", "$prefix.:format", array(
      ':controller' => $options['controller'], ':action' => 'create', 
      'conditions' => array('method' => 'POST')
    ));
    
    $this->named("update_{$options['name_prefix']}{$options['singular']}", "$prefix/:id.:format", array(
      ':controller' => $options['controller'], ':action' => 'update', 
      'conditions' => array('method' => 'PUT'), 'requirements' => array(':id' => '\d+')
    ));
    
    $this->named("delete_{$options['name_prefix']}{$options['singular']}", "$prefix/:id.:format", array(
      ':controller' => $options['controller'], ':action' => 'delete', 
      'conditions' => array('method' => 'DELETE'), 'requirements' => array(':id' => '\d+')
    ));
    
    if (isset($options['has_one']))
    {
      foreach(array_collection($options['has_one']) as $nested_name)
      {
        $this->resource($nested_name, array(
          'name_prefix' => "{$options['singular']}_",
          'path_prefix' => "$name/:{$options['singular']}_id"
        ));
      }
    }
    
    if (isset($options['has_many']))
    {
      foreach(array_collection($options['has_many']) as $nested_name)
      {
        $this->resources($nested_name, array(
          'name_prefix' => "{$options['singular']}_",
          'path_prefix' => "$name/:{$options['singular']}_id"
        ));
      }
    }
  }
}

?>
