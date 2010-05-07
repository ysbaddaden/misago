<?php
namespace Misago\ActionController\Routing;
use Misago\ActiveSupport\String;

# =RESTful routes
# 
# A resource is a pair of controller/model with a REST logic in routes.
# Declaring a resource will create a bunch of named routes.
# 
# See <tt>resources</tt> and <tt>resource</tt> for additional help.
# 
# ==Nested routes
# 
# Relations are great, but often come with ugly URL like
# +/tickets/new?event_id=123+. With nested routes you may create better
# URL and helper methods. For instance:
# 
#   $map->resource('events', array('has_many' => 'tickets'));
# 
# This will create the following named routes:
# 
#   event_tickets      /events/:event_id/tickets/new
#   new_event_ticket   /events/:event_id/tickets/:id
#   edit_event_ticket  /events/:event_id/tickets/:id/edit
#   event_ticket       /events/:event_id/tickets/:id
# 
# You may achieve nested resources the following ways:
# 
#   # using +has_many+ or +has_one+:
#   $map->resources('events', array('has_many' => 'tickets', 'has_one' => 'tag'));
#   
#   # using closures:
#   $map->resources('events', function($event)
#   {
#     $event->resources('tickets');
#     $event->resource('tag');
#   });
#   
#   # using +path_prefix+ (not recommended):
#   $map->resources('events');
#   $map->resources('tickets', array('path_prefix' => 'events/:id'));
#   $map->resource('tag',      array('path_prefix' => 'events/:id'));
# 
# TODO: namespaced resources.
# IMPROVE: :path_names option
# IMPROVE: config.action_controller.resources_path_names
#
class ResourceRoutes extends \Misago\Object
{
  # TODO: Check if named route $plural exists before defining POST!
  # TODO: Check if named route $singular exists before defining PUT/DELETE!
  function resources($name, $options=array(), $closure=null)
  {
    if (is_object($options))
    {
      $closure = $options;
      $options = array();
    }
    $options  = array_merge(array('name_prefix' => ''), $options);
    $plural   = $name;
    $singular = isset($options['singular']) ? $options['singular'] : String::singularize($name);
    
    $plural_path = isset($options['as']) ? $options['as'] : $plural;
    if (isset($options['path_prefix'])) {
      $plural_path = "{$options['path_prefix']}/$plural_path";
    }
    
    $plural_name   = $options['name_prefix'].$plural;
    $singular_name = $options['name_prefix'].$singular;
    $controller    = isset($options['controller'])  ? $options['controller'] : $plural;
    
    # only/except
    $actions = isset($options['only']) ? array_collection($options['only']) :
      array('index', 'new', 'create', 'show', 'edit', 'update', 'delete');
    if (isset($options['except'])) {
      $actions = array_diff($actions, array_collection($options['except']));
    }
    
    if (isset($options['collection']))
    {
      foreach($options['collection'] as $action => $method)
      {
        $this->named("{$action}_{$plural_name}", "$plural_path/$action", array(
          ':controller' => $controller,
          ':action'     => $action,
          'conditions'  => array('method' => $method)
        ));
        $this->named("formatted_{$action}_{$plural_name}", "$plural_path/$action.:format", array(
          ':controller' => $controller,
          ':action'     => $action,
          'conditions'  => array('method' => $method)
        ));
      }
    }
    if (in_array('index', $actions))
    {
      $index_name = ($plural == $singular) ? "{$plural_name}_index" : $plural_name;
      $this->named($index_name, $plural_path, array(
        ':controller' => $controller,
        ':action'     => 'index',
        'conditions'  => array('method' => 'GET')
      ));
      $this->named("formatted_{$index_name}", "$plural_path.:format", array(
        ':controller' => $controller,
        ':action'     => 'index',
        'conditions'  => array('method' => 'GET')
      ));
    }
    if (in_array('create', $actions))
    {
      $this->connect($plural_path, array(
        ':controller' => $controller,
        ':action'     => 'create',
        'conditions'  => array('method' => 'POST')
      ));
      $this->connect("$plural_path.:format", array(
        ':controller' => $controller,
        ':action'     => 'create',
        'conditions'  => array('method' => 'POST')
      ));
    }
    if (in_array('new', $actions))
    {
      $this->named("new_$singular_name", "$plural_path/new", array(
        ':controller' => $controller,
        ':action'     => 'neo',
        'conditions'  => array('method' => 'GET')
      ));
      $this->named("formatted_new_$singular_name", "$plural_path/new.:format", array(
        ':controller' => $controller,
        ':action'     => 'neo',
        'conditions'  => array('method' => 'GET')
      ));
    }
    
    if (isset($options['member']))
    {
      foreach($options['member'] as $action => $method)
      {
        $this->named("{$action}_{$singular_name}", "$plural_path/:id/$action", array(
          ':controller' => $controller,
          ':action'     => $action,
          'conditions'  => array('method' => $method)
        ));
        $this->named("formatted_{$action}_{$singular_name}", "$plural_path/:id/$action.:format", array(
          ':controller' => $controller,
          ':action'     => $action,
          'conditions'  => array('method' => $method)
        ));
      }
    }
    if (in_array('edit', $actions))
    {
      $this->named("edit_$singular_name", "$plural_path/:id/edit", array(
        ':controller' => $controller,
        ':action'     => 'edit',
        'conditions'  => array('method' => 'GET')
      ));
      $this->named("formatted_edit_$singular_name", "$plural_path/:id/edit.:format", array(
        ':controller' => $controller,
        ':action'     => 'edit',
        'conditions'  => array('method' => 'GET')
      ));
    }
    if (in_array('show', $actions))
    {
      $this->named($singular_name, "$plural_path/:id", array(
        ':controller' => $controller,
        ':action' => 'show',
        'conditions' => array('method' => 'GET')
      ));
      $this->named("formatted_$singular_name", "$plural_path/:id.:format", array(
        ':controller' => $controller,
        ':action' => 'show',
        'conditions' => array('method' => 'GET')
      ));
    }
    if (in_array('update', $actions))
    {
      $this->connect("$plural_path/:id", array(
        ':controller' => $controller,
        ':action'     => 'update',
        'conditions'  => array('method' => 'PUT')
      ));
      $this->connect("$plural_path/:id.:format", array(
        ':controller' => $controller,
        ':action'     => 'update',
        'conditions'  => array('method' => 'PUT')
      ));
    }
    if (in_array('delete', $actions))
    {
      $this->connect("$plural_path/:id", array(
        ':controller' => $controller,
        ':action'     => 'delete',
        'conditions' => array('method' => 'DELETE')
      ));
      $this->connect("$plural_path/:id.:format", array(
        ':controller' => $controller,
        ':action'     => 'delete',
        'conditions' => array('method' => 'DELETE')
      ));
    }
    
    # nested resource
    if (isset($options['has_one']))
    {
      foreach(array_collection($options['has_one']) as $nested_name)
      {
        $nested_options = array(
          'name_prefix' => "{$singular_name}_",
          'path_prefix' => "$plural_path/:{$singular}_id",
        );
#        if (isset($options['namespace'])) {
#          $nested_options['namespace'] = $options['namespace'];
#        }
        $this->resource($nested_name, $nested_options);
      }
    }
    
    # nested resources
    if (isset($options['has_many']))
    {
      foreach(array_collection($options['has_many']) as $nested_name)
      {
        $nested_options = array(
          'name_prefix' => "{$singular_name}_",
          'path_prefix' => "$plural_path/:{$singular}_id",
        );
#        if (isset($options['namespace'])) {
#          $nested_options['namespace'] = $options['namespace'];
#        }
        $this->resources($nested_name, $nested_options);
      }
    }
    
    # manual nesting
    if (is_object($closure))
    {
      $name_prefix = "{$singular_name}_";
      $path_prefix = "{$plural_path}/:{$singular}_id";
#      $namespace   = isset($options['namespace']) ? $options['namespace'] : null;
      $obj         = new Nested($this, $name_prefix, $path_prefix/*, $namespace*/);
      $closure($obj);
    }
  }
  
  # TODO: Check if named route $singular exists before defining POST/PUT/DELETE!
  function resource($name, $options=array(), $closure=null)
  {
    if (is_object($options))
    {
      $closure = $options;
      $options = array();
    }
    $options  = array_merge(array('name_prefix' => ''), $options);
    $singular = $name;
    $plural   = isset($options['plural']) ? $options['plural'] : String::singularize($name);
    
    $singular_path = isset($options['as']) ? $options['as'] : $singular;
    if (isset($options['path_prefix'])) {
      $singular_path = "{$options['path_prefix']}/$singular_path";
    }
    
    $singular_name = $options['name_prefix'].$singular;
    $plural_name   = $options['name_prefix'].$plural;
    $controller    = isset($options['controller'])  ? $options['controller'] : $plural;
    
    $actions = isset($options['only']) ? array_collection($options['only']) :
      array('new', 'create', 'show', 'edit', 'update', 'delete');
    if (isset($options['except'])) {
      $actions = array_diff($actions, array_collection($options['except']));
    }
    
    if (isset($options['collection']))
    {
      foreach($options['collection'] as $action => $method)
      {
        $this->named("{$action}_{$singular_name}", "$singular_path/$action", array(
          ':controller' => $controller,
          ':action'     => $action,
          'conditions'  => array('method' => $method)
        ));
        $this->named("formatted_{$action}_{$singular_name}", "$singular_path/$action.:format", array(
          ':controller' => $controller,
          ':action'     => $action,
          'conditions'  => array('method' => $method)
        ));
      }
    }
    if (in_array('show', $actions))
    {
      $this->named($singular_name, $singular_path, array(
        ':controller' => $controller,
        ':action'     => 'show',
        'conditions' => array('method' => 'GET')
      ));
      $this->named("formatted_$singular_name", "$singular_path.:format", array(
        ':controller' => $controller,
        ':action'     => 'show',
        'conditions' => array('method' => 'GET')
      ));
    }
    if (in_array('new', $actions))
    {
      $this->named("new_$singular_name", "$singular_path/new", array(
        ':controller' => $controller,
        ':action'     => 'neo',
        'conditions'  => array('method' => 'GET')
      ));
      $this->named("formatted_new_$singular_name", "$singular_path/new.:format", array(
        ':controller' => $controller,
        ':action'     => 'neo',
        'conditions'  => array('method' => 'GET')
      ));
    }
    if (in_array('create', $actions))
    {
      $this->connect($singular_path, array(
        ':controller' => $controller,
        ':action'     => 'create',
        'conditions'  => array('method' => 'POST')
      ));
      $this->connect("$singular_path.:format", array(
        ':controller' => $controller,
        ':action'     => 'create',
        'conditions'  => array('method' => 'POST')
      ));
    }
    if (isset($options['member']))
    {
      foreach($options['member'] as $action => $method)
      {
        $this->named("{$action}_{$singular_name}", "$singular_path/:id/$action", array(
          ':controller' => $controller,
          ':action'     => $action,
          'conditions'  => array('method' => $method)
        ));
        $this->named("formatted_{$action}_{$singular_name}", "$singular_path/:id/$action.:format", array(
          ':controller' => $controller,
          ':action'     => $action,
          'conditions'  => array('method' => $method)
        ));
      }
    }
    if (in_array('edit', $actions))
    {
      $this->named("edit_$singular_name", "$singular_path/edit", array(
        ':controller' => $controller,
        ':action'     => 'edit',
        'conditions'  => array('method' => 'GET')
      ));
      $this->named("formatted_edit_$singular_name", "$singular_path/edit.:format", array(
        ':controller' => $controller,
        ':action'     => 'edit',
        'conditions'  => array('method' => 'GET')
      ));
    }
    if (in_array('update', $actions))
    {
      $this->connect($singular_path, array(
        ':controller' => $controller,
        ':action'     => 'update',
        'conditions'  => array('method' => 'PUT')
      ));
      $this->connect("$singular_path.:format", array(
        ':controller' => $controller,
        ':action'     => 'update',
        'conditions'  => array('method' => 'PUT')
      ));
    }
    if (in_array('delete', $actions))
    {
      $this->connect($singular_path, array(
        ':controller' => $controller,
        ':action'     => 'delete',
        'conditions'  => array('method' => 'DELETE')
      ));
      $this->connect("$singular_path.:format", array(
        ':controller' => $controller,
        ':action'     => 'delete',
        'conditions'  => array('method' => 'DELETE')
      ));
    }
    
    # nested resource
    if (isset($options['has_one']))
    {
      foreach(array_collection($options['has_one']) as $nested_name)
      {
        $nested_options = array(
          'name_prefix' => "{$singular_name}_",
          'path_prefix' => "$singular_path/:{$singular}_id",
        );
#        if (isset($options['namespace'])) {
#          $nested_options['namespace'] = $options['namespace'];
#        }
        $this->resource($nested_name, $nested_options);
      }
    }
    
    # nested resources
    if (isset($options['has_many']))
    {
      foreach(array_collection($options['has_many']) as $nested_name)
      {
        $nested_options = array(
          'name_prefix' => "{$singular_name}_",
          'path_prefix' => "$singular_path/:{$singular}_id",
        );
#        if (isset($options['namespace'])) {
#          $nested_options['namespace'] = $options['namespace'];
#        }
        $this->resources($nested_name, $nested_options);
      }
    }
    
    # manual nesting
    if (is_object($closure))
    {
      $name_prefix = "{$singular_name}_";
#      $namespace   = isset($options['namespace']) ? $options['namespace'] : null;
      $obj = new Nested($this, $name_prefix, $singular_path/*, $namespace*/);
      $closure($obj);
    }
  }
  
  /*
  # Singleton resource. Resource name must always be singular, but the
  # controller & index use the plural form.
  # 
  #   $map->resource('account');
  # 
  # This will create the following named routes:
  # 
  #   accounts        GET     /accounts          => AccountsController::index()
  #   new_account     GET     /accounts/new      => AccountsController::neo()
  #   create_account  POST    /accounts          => AccountsController::create()
  #   edit_account    GET     /account/:id/edit  => AccountsController::edit()
  #   show_account    GET     /account/:id       => AccountsController::show()
  #   update_account  PUT     /account/:id       => AccountsController::update()
  #   delete_account  DELETE  /account/:id       => AccountsController::delete()
  # 
  # Available options:
  # 
  # - +as+          - use this name for the path instead
  # - +collection+  - a hash of additional collection methods {action => method}
  # - +controller+  - force controller's name (defaults to plural name)
  # - +except+      - list of routes to skip (eg: ['update', 'delete'])
  # - +has_one+     - declare a nested singleton resource
  # - +has_many+    - declare a nested collection resource
  # - +name_prefix+ - particular prefix for routes' name
  # - +member+      - same as +collection+ but applies to a particular id
  # - +only+        - list of routes to generate (eg: ['index', 'show'])
  # - +path_prefix+ - a particular prefix for routes' path
  # - +singular+    - force singular name
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
    $options['plural_prefix'] = isset($options['as']) ? $options['as'] : $options['plural'];
    
    $this->build_resource($name, $options, $closure);
  }
  
  private function build_resource($name, $options, $closure)
  {
    if (!isset($options['name_prefix'])) $options['name_prefix'] = '';
    
    $singular_name = "{$options['name_prefix']}{$options['singular']}";
    $prefix = isset($options['path_prefix']) ?
      $options['path_prefix'].'/'.$options['prefix'] : $options['prefix'];
    $plural_prefix = isset($options['path_prefix']) ?
      $options['path_prefix'].'/'.$options['plural_prefix'] : $options['plural_prefix'];
    $controller = isset($options['namespace']) ?
      $options['namespace'].$options['controller'] : $options['controller'];
    
    # list of collection/member actions
    $collection = array(
      'index'  => 'get',
      'new'    => 'get',
      'create' => 'post'
    );
    if (!empty($options['collection'])) {
      $collection = array_merge($options['collection'], $collection);
    }
    
    $member = array(
      'show'   => 'get',
      'edit'   => 'get',
      'update' => 'put',
      'delete' => 'delete'
    );
    if (!empty($options['member'])) {
      $member = array_merge($options['member'], $member);
    }
    
    if (!empty($options['only']))
    {
      $collection = array_intersect_key($collection, array_flip($options['only']));
      $member     = array_intersect_key($member,     array_flip($options['only']));
    }
    if (!empty($options['except']))
    {
      $collection = array_diff_key($collection, array_flip($options['except']));
      $member     = array_diff_key($member,     array_flip($options['except']));
    }
    
    # collection actions like /members[/:action][.:format]
    foreach($collection as $action => $method)
    {
      switch($action)
      {
        case 'index':
          $_name = $options['name_prefix'].$options['plural'];
          $_path = "{$plural_prefix}.:format";
        break;
        
        case 'new':
          $_name = $action.'_'.$singular_name;
          $_path = "{$plural_prefix}/$action.:format";
        break;
        
        case 'create':
          $_name = $action.'_'.$singular_name;
          $_path = "{$plural_prefix}.:format";
        break;
        
        default:
          $_name = $action.'_'.$options['plural'];
          $_path = "{$plural_prefix}/$action.:format";
      }
      $_options = array(
        ':controller' => $controller,
        ':action'     => ($action == 'new') ? 'neo' : $action
      );
      if ($method != 'any') {
        $_options['conditions'] = array('method' => strtoupper($method));
      }
      $this->named($_name, $_path, $_options);
    }
    
    # member actions like /members/:id[/:action][.:format]
    foreach($member as $action => $method)
    {
      switch($action)
      {
        case 'show': case 'update': case 'delete':
          $_name = $action.'_'.$singular_name;
          $_path = "$prefix/:id.:format";
        break;
        
        default:
          $_name = $action.'_'.$singular_name;
          $_path = "$prefix/:id/$action.:format";
      }
      $_options = array(
        ':controller'  => $controller,
        ':action'      => $action,
        'requirements' => array(':id' => '\d+')
      );
      if ($method != 'any') {
        $_options['conditions'] = array('method' => strtoupper($method));
      }
      $this->named($_name, $_path, $_options);
    }
    
    # nested resource
    if (isset($options['has_one']))
    {
      foreach(array_collection($options['has_one']) as $nested_name)
      {
        $nested_options = array(
          'name_prefix' => "{$singular_name}_",
          'path_prefix' => "$prefix/:{$options['singular']}_id",
        );
        if (isset($options['namespace'])) {
          $nested_options['namespace'] = $options['namespace'];
        }
        $this->resource($nested_name, $nested_options);
      }
    }
    
    # nested resources
    if (isset($options['has_many']))
    {
      foreach(array_collection($options['has_many']) as $nested_name)
      {
        $nested_options = array(
          'name_prefix' => "{$singular_name}_",
          'path_prefix' => "$prefix/:{$options['singular']}_id",
        );
        if (isset($options['namespace'])) {
          $nested_options['namespace'] = $options['namespace'];
        }
        $this->resource($nested_name, $nested_options);
      }
    }
    
    # manual nesting
    if (is_object($closure))
    {
      $name_prefix = "{$singular_name}_";
      $path_prefix = "{$prefix}/:{$options['singular']}_id";
      $namespace   = isset($options['namespace']) ? $options['namespace'] : null;
      $obj = new Nested($this, $name_prefix, $path_prefix, $namespace);
      $closure($obj);
    }
  }
  */
}

?>
