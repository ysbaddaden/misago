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
# IMPROVE: :path_names option
# IMPROVE: config.action_controller.resources_path_names
#
class ResourceRoutes extends \Misago\Object
{
  # Creates a RESTful resources. The name must be plural.
  # 
  #   $map->resources('accounts');
  # 
  # It will generate the following routes:
  # 
  #       accounts GET    /accounts          AccountsController->index()
  #    new_account GET    /accounts/new      AccountsController->neo()
  #                POST   /accounts          AccountsController->create()
  #        account GET    /accounts/:id      AccountsController->show()
  #   edit_account GET    /accounts/:id/edit AccountsController->edit()
  #                PUT    /accounts/:id      AccountsController->update()
  #                DELETE /accounts/:id      AccountsController->delete()
  # 
  # =Form helpers
  # 
  # You may use the generated named routes to link to the resources:
  # 
  #   <\?= link_to("all accounts", accounts_path()) ?\>
  #   <\?= link_to("new account", new_account_path()) ?\>
  #   <\?= link_to($account->name, account_path($account)) ?\>
  #   <\?= link_to($account->name, account_path(array(':id' => $account->id))) ?\>
  #   <\?= link_to("modify your account", edit_account_path($account)) ?\>
  # 
  #   # deletion link:
  #   <\?= link_to("delete your account", account_path($account),
  #     array('method' => 'delete', 'confirm' => "Are you sure?")) ?\>
  # 
  # You may create forms this way:
  # 
  #   # deletion button
  #   <\?= button_to("delete your account", account_path($account), array('method' => 'delete') ?\>
  #   
  #   # update form:
  #   <\? $f = form_for($account) ?\>
  #   <\?= $f->start(account_path(), array('method' => 'put')) ?\>
  #     <\?= $this->render(array('partial' => 'form', 'locals' => array('f' => $f))) ?\>
  #   <\?= $f->end() ?\>
  # 
  # You may also rely on the record's state (thanks to +new_record+), and let
  # +form_for()+ decide by itself what to do (either create or update):
  # 
  #   # this one will generate a POST form (create) on account_path() URL:
  #   <\? $f = form_for(new Account()) ?\>
  #   <\?= $f->start() ?\>
  #     <\?= $this->render(array('partial' => 'form', 'locals' => array('f' => $f))) ?\>
  #   <\?= $f->end() ?\>
  #   
  #   # while this one will generate a PUT form (update) on account_path() URL:
  #   <\? $f = form_for(Account(5)) ?\>
  #   <\?= $f->start() ?\>
  #     <\?= $this->render(array('partial' => 'form', 'locals' => array('f' => $f))) ?\>
  #   <\?= $f->end() ?\>
  # 
  # = Options:
  # 
  # * +as+          - use another path for the route. For instance +resource('profile', array('as' => 'benutzerprofil'))+ will generate routes like +"/benutzerprofil/edit"+ using +ProfilesController+.
  # * +collection+  - additional resources actions in an +{action => pair}+ hash.
  # * +controller+  - specify the controller to use (may be a namespaced one using +admin\controller+)
  # * +except+      - an array of actions to skip. For instance +{'only' => ['update', 'delete']}+ will skip the +update+ and +delete+ routes.
  # * +has_many+    - 
  # * +has_one+     - 
  # * +member+      - same as collection, but for a particular resource.
  # * +name_prefix+ - 
  # * +only+        - limits actions to this list. For instance +{'only' => ['index', 'show']}+ will generate the index and show routes only.
  # * +path_prefix+ - 
  # * +plural+      - specify the plural name of the resource
  # 
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
    
    $controller = isset($options['controller']) ? $options['controller'] : $plural;
    if (isset($options['name_space'])) {
      $controller = $options['name_space'].'\\'.$controller;
    }
    
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
        ':action'     => 'show',
        'conditions'  => array('method' => 'GET')
      ));
      $this->named("formatted_$singular_name", "$plural_path/:id.:format", array(
        ':controller' => $controller,
        ':action'     => 'show',
        'conditions'  => array('method' => 'GET')
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
        'conditions'  => array('method' => 'DELETE')
      ));
      $this->connect("$plural_path/:id.:format", array(
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
          'path_prefix' => "$plural_path/:{$singular}_id",
        );
        if (isset($options['name_space'])) {
          $nested_options['name_space'] = $options['name_space'];
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
          'path_prefix' => "$plural_path/:{$singular}_id",
        );
        if (isset($options['name_space'])) {
          $nested_options['name_space'] = $options['name_space'];
        }
        $this->resources($nested_name, $nested_options);
      }
    }
    
    # manual nesting
    if (is_object($closure))
    {
      $name_prefix = "{$singular_name}_";
      $path_prefix = "{$plural_path}/:{$singular}_id";
      $name_space  = isset($options['name_space']) ? $options['name_space'] : null;
      $obj         = new Nested($this, $name_prefix, $path_prefix, $name_space);
      $closure($obj);
    }
  }
  
  # Create a singleton resource within a global context. For instance their
  # might a single +/profile+ connected to the currently authentified member.
  # 
  # The resource must always be singular, but the controller will be plural.
  # 
  # It will generate the following routes:
  # 
  #        profile GET    /profile      ProfilesController->show()
  #    new_profile GET    /profile/new  ProfilesController->neo()
  #                POST   /profile      ProfilesController->create()
  #   edit_profile GET    /profile/edit ProfilesController->edit()
  #                PUT    /profile      ProfilesController->update()
  #                DELETE /profile      ProfilesController->delete()
  # 
  # See <tt>resources</tt> for parameters and options. The main differences are:
  # 
  # * there is no +singular+ option, but a +plural+ one;
  # * there is no index route;
  # * nesting resource(s) will use the singular name as path prefix.
  # 
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
    $plural   = isset($options['plural']) ? $options['plural'] : String::pluralize($name);
    
    $singular_path = isset($options['as']) ? $options['as'] : $singular;
    if (isset($options['path_prefix'])) {
      $singular_path = "{$options['path_prefix']}/$singular_path";
    }
    
    $singular_name = $options['name_prefix'].$singular;
    $plural_name   = $options['name_prefix'].$plural;
    
    $controller = isset($options['controller']) ? $options['controller'] : $plural;
    if (isset($options['name_space'])) {
      $controller = $options['name_space'].'\\'.$controller;
    }
    
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
        'conditions'  => array('method' => 'GET')
      ));
      $this->named("formatted_$singular_name", "$singular_path.:format", array(
        ':controller' => $controller,
        ':action'     => 'show',
        'conditions'  => array('method' => 'GET')
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
        if (isset($options['name_space'])) {
          $nested_options['name_space'] = $options['name_space'];
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
          'path_prefix' => "$singular_path/:{$singular}_id",
        );
        if (isset($options['name_space'])) {
          $nested_options['name_space'] = $options['name_space'];
        }
        $this->resources($nested_name, $nested_options);
      }
    }
    
    # manual nesting
    if (is_object($closure))
    {
      $name_prefix = "{$singular_name}_";
      $name_space  = isset($options['name_space']) ? $options['name_space'] : null;
      $obj = new Nested($this, $name_prefix, $singular_path, $name_space);
      $closure($obj);
    }
  }
  
  # Easily create resource(s) within a same folder. For instance moving
  # administration files to +/admin+:
  # 
  #   $map->name_space('admin', function($admin) {
  #     $admin->resources('products');
  #   }
  # 
  # This will require the controller +Admin\ProductsController+
  # (as +app/controllers/Admin/ProductsController.php+).
  # 
  # Please note that it's just an easier way to write resource(s) with particular
  # +name_prefix+ and +path_prefix+ options. It will for instance create the
  # following routes:
  # 
  #   admin_products      admin/products          Admin\ProductsController::index()
  #   new_admin_product   admin/product/new       Admin\ProductsController::neo()
  #   admin_product       admin/product/:id       Admin\ProductsController::show()
  #   edit_admin_product  admin/product/:id       Admin\ProductsController::edit()
  # 
  function name_space($name, $closure)
  {
    $obj = new Nested($this, "{$name}_", $name, $name);
    $closure($obj);
  }
}

?>
