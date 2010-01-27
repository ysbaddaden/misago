<?php
namespace Misago\ActionController\Routing;
use Misago\ActiveSupport\String;

# =RESTful routes
# 
# A resource is a pair of controller/model with a REST logic in routes.
# Declaring a resource will create a bunch of named routes.
# 
# See <tt>resource</tt> for additional help.
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
#     $event->resource('tag');
#   });
# 
#   # using +path_prefix+ (not recommended):
#   $map->resource('event');
#   $map->resource('ticket', array('path_prefix' => 'event/:id'));
# 
class ResourceRoutes extends \Misago\Object
{
  # Singleton resource. Resource name must always be singular, but the
  # controller & index use the plural form.
  # 
  #   $map->resource('account');
  # 
  # This will create the following named routes:
  # 
  #   accounts        GET     /accounts          => AccountsController::index()
  #   new_account     GET     /account/new       => AccountsController::neo()
  #   create_account  POST    /account           => AccountsController::create()
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
  # FIXME: 'only' causes 'collection' & 'member' to not generate routes!
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
    $collection = array('index' => 'get', 'new' => 'get', 'create' => 'post');
    if (!empty($options['collection'])) {
      $collection = array_merge($options['collection'], $collection);
    }
    $member = array('show' => 'get', 'edit' => 'get', 'update' => 'put', 'delete' => 'delete');
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
        
        case 'create':
          $_name = $action.'_'.$singular_name;
          $_path = "$prefix.:format";
        break;
        
        default:
          $_name = $action.'_'.$singular_name;
          $_path = "$prefix/$action.:format";
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
}

?>
