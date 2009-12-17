<?php
namespace Misago\ActionController\Routing;
use Misago\ActiveSupport\String;

# =RESTful routes
# 
# A resource is a pair of controller/model with a REST logic in routes.
# Declaring a resource will create a bunch of named routes.
# 
# See <tt>resource</tt> and <tt>resources</tt> for additional help.
# 
# Attention: in RESTful routes +:id+ must be an integer.
# 
# ==Nested routes
# 
# Relations are great, but often come with ugly URL like
# +/tickets/new?event_id=123+. With nested routes you may create better
# URL and help methods. For instance:
# 
#   $map->resource('event', array('has_many' => 'tickets'));
# 
# This will create named routes like:
# 
#   event_tickets      /event/:event_id/tickets/new
#   new_event_ticket   /event/:event_id/tickets/:id
#   edit_event_ticket  /event/:event_id/tickets/:id/edit
#   etc.
# 
# You may also achieve nested resources the following ways:
# 
#   # using +has_many+:
#   $map->resource('event', array('has_many' => 'tickets'));
#   
#   # using closures:
#   $map->resource('event', function($event) {
#     $event->resources('tags');
#   });
# 
#   # using +path_prefix+ (not recommended):
#   $map->resource('event');
#   $map->resources('tickets', array('path_prefix' => 'ticket/:id'));
# 
class ResourceRoutes extends \Misago\Object
{
  # Singleton resource. Resource name must always be singular, but the
  # controller uses the plural form.
  # 
  #   $map->resource('account');
  # 
  # This will create the following named routes:
  # 
  #   account         GET     /account           => AccountsController::index()
  #   new_account     GET     /account/new       => AccountsController::neo()
  #   create_account  POST    /account           => AccountsController::create()
  #   edit_account    GET     /account/:id/edit  => AccountsController::edit()
  #   show_account    GET     /account/:id       => AccountsController::show()
  #   update_account  PUT     /account/:id       => AccountsController::update()
  #   delete_account  DELETE  /account/:id       => AccountsController::delete()
  # 
  # See <tt>resources</tt> for help on options, except that there's no
  # +singular+ option, but a +plural+ option instead.
  # 
  function resource($name, $options=array(), $closure=null)
  {
    if (is_object($options))
    {
      $closure = $options;
      $options = array();
    }
    
    $options['singular'] = $name;
    if (empty($options['plural']))      $options['plural']      = String::pluralize($name);
    if (empty($options['controller']))  $options['controller']  = $options['plural'];
    $options['prefix'] = isset($options['as']) ? $options['as'] : $options['singular'];
    
    $this->build_resource($name, $options, $closure);
  }
  
  # Collection resource. Resource name must always be plural.
  # 
  #   $map->resources('accounts');
  # 
  # This will create the following named routes:
  # 
  #   accounts         GET     /accounts           => AccountsController::index()
  #   new_accounts     GET     /accounts/new       => AccountsController::neo()
  #   create_accounts  POST    /accounts           => AccountsController::create()
  #   edit_accounts    GET     /accounts/:id/edit  => AccountsController::edit()
  #   show_accounts    GET     /accounts/:id       => AccountsController::show()
  #   update_accounts  PUT     /accounts/:id       => AccountsController::update()
  #   delete_accounts  DELETE  /accounts/:id       => AccountsController::delete()
  # 
  # Available options:
  # 
  # - +as+          - use this name for the path instead
  # - +controller+  - force controller's name (defaults to plural name)
  # - +has_one+     - declare a nested singleton resource
  # - +has_many+    - declare a nested collection resource
  # - +name_prefix+ - particular prefix for routes' name
  # - +only+        - list of routes to generate (eg: ['index', 'show'])
  # - +except+      - list of routes to skip (eg: ['update', 'delete'])
  # - +path_prefix+ - a particular prefix for routes' path
  # - +singular+    - force singular name
  # 
  function resources($name, $options=array(), $closure=null)
  {
    if (is_object($options))
    {
      $closure = $options;
      $options = array();
    }
    
    $options['plural'] = $name;
    if (empty($options['singular']))    $options['singular']    = String::singularize($name);
    if (empty($options['controller']))  $options['controller']  = $options['plural'];
    $options['prefix'] = isset($options['as']) ? $options['as'] : $options['plural'];
    
    $this->build_resource($name, $options, $closure);
  }
  
  private function build_resource($name, $options, $closure)
  {
    if (!isset($options['only']))        $options['only']   = array('index', 'new', 'show', 'edit', 'create', 'update', 'delete');
    if (!isset($options['except']))      $options['except'] = array();
    if (!isset($options['name_prefix'])) $options['name_prefix'] = '';
    
    $prefix = isset($options['path_prefix']) ?
      $options['path_prefix'].'/'.$options['prefix'] : $options['prefix'];
    $controller = isset($options['controller_prefix']) ?
      $options['controller_prefix'].$options['controller'] : $options['controller'];
    
    if (in_array('index', $options['only'])
      and !in_array('index', $options['except']))
    {
      $this->named("{$options['name_prefix']}$name", "$prefix.:format", array(
        ':controller' => $controller, ':action' => 'index',
        'conditions' => array('method' => 'GET')
      ));
    }
    
    if (in_array('new', $options['only'])
      and !in_array('new', $options['except']))
    {
      $this->named("new_{$options['name_prefix']}{$options['singular']}", "$prefix/new.:format", array(
        ':controller' => $controller, ':action' => 'neo',    
        'conditions' => array('method' => 'GET')
      ));
    }
    
    if (in_array('show', $options['only'])
      and !in_array('show', $options['except']))
    {
      $this->named("show_{$options['name_prefix']}{$options['singular']}", "$prefix/:id.:format", array(
        ':controller' => $controller, ':action' => 'show',   
        'conditions' => array('method' => 'GET'), 'requirements' => array(':id' => '\d+')
      ));
    }
    
    if (in_array('edit', $options['only'])
      and !in_array('edit', $options['except']))
    {
      $this->named("edit_{$options['name_prefix']}{$options['singular']}", "$prefix/:id/edit.:format", array(
        ':controller' => $controller, ':action' => 'edit',   
        'conditions' => array('method' => 'GET'), 'requirements' => array(':id' => '\d+')
      ));
    }
    
    if (in_array('create', $options['only'])
      and !in_array('create', $options['except']))
    {
      $this->named("create_{$options['name_prefix']}{$options['singular']}", "$prefix.:format", array(
        ':controller' => $controller, ':action' => 'create', 
        'conditions' => array('method' => 'POST')
      ));
    }
    
    if (in_array('update', $options['only'])
      and !in_array('update', $options['except']))
    {
      $this->named("update_{$options['name_prefix']}{$options['singular']}", "$prefix/:id.:format", array(
        ':controller' => $controller, ':action' => 'update', 
        'conditions' => array('method' => 'PUT'), 'requirements' => array(':id' => '\d+')
      ));
    }
    
    if (in_array('delete', $options['only'])
      and !in_array('delete', $options['except']))
    {
      $this->named("delete_{$options['name_prefix']}{$options['singular']}", "$prefix/:id.:format", array(
        ':controller' => $controller, ':action' => 'delete', 
        'conditions' => array('method' => 'DELETE'), 'requirements' => array(':id' => '\d+')
      ));
    }
    
    if (isset($options['has_one']))
    {
      foreach(array_collection($options['has_one']) as $nested_name)
      {
        $nested_options = array(
          'name_prefix' => "{$options['name_prefix']}{$options['singular']}_",
          'path_prefix' => "$prefix/:{$options['singular']}_id",
        );
        if (isset($options['controller_prefix'])) {
          $nested_options['controller_prefix'] = $options['controller_prefix'];
        }
        $this->resource($nested_name, $nested_options);
      }
    }
    
    if (isset($options['has_many']))
    {
      foreach(array_collection($options['has_many']) as $nested_name)
      {
        $nested_options = array(
          'name_prefix' => "{$options['name_prefix']}{$options['singular']}_",
          'path_prefix' => "$prefix/:{$options['singular']}_id",
        );
        if (isset($options['controller_prefix'])) {
          $nested_options['controller_prefix'] = $options['controller_prefix'];
        }
        $this->resources($nested_name, array(
          'name_prefix' => "{$options['name_prefix']}{$options['singular']}_",
          'path_prefix' => "$prefix/:{$options['singular']}_id"
        ));
      }
    }
    
    if (is_object($closure))
    {
      $name_prefix       = "{$options['name_prefix']}{$options['singular']}_";
      $path_prefix       = "{$prefix}/:{$options['singular']}_id";
      $controller_prefix = isset($options['controller_prefix']) ?
        $options['controller_prefix'] : null;
      
      $obj = new Nested($this, $name_prefix, $path_prefix, $controller_prefix);
      $closure($obj);
    }
  }
}

?>
