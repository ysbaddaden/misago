<?php
namespace Misago\Generator;
use Misago\ActiveSupport\String;

class Resource extends Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate resource <name>\n";
      exit;
    }
    $this->options = $options;
    
    $Controller = String::camelize(String::pluralize(String::singularize(String::underscore($args[0]))));
    $resource   = String::underscore($Controller);
    $view_path  = String::underscore($filename);
    $Model      = String::singularize($Controller);
    $table      = String::pluralize(String::underscore($Model));
    
    $vars = array(
      'Controller'   => $Controller,
      'controller'   => String::underscore($Controller),
      'view_path'    => $view_path,
      'Model'        => $Model,
      'model'        => String::underscore($Model),
      'model_plural' => String::pluralize(String::underscore($Model)),
      'table'        => $table,
    );
    
    $this->create_directory('app/controllers');
    $this->create_directory('app/helpers');
    $this->create_directory("app/views/{$view_path}");
    $this->create_directory('app/models');
    $this->create_directory('test/functional');
    $this->create_directory('test/unit');
    $this->create_directory('test/fixtures');
    
    # controller
    $this->create_file_from_template("app/controllers/{$Controller}Controller.php",     'resource/controller.php', $vars);
    $this->create_file_from_template("test/functional/test_{$Controller}Controller.php", 'resource/test_controller.php', $vars);
    $this->create_file_from_template("app/helpers/{$Controller}Helper.php",             'resource/helper.php', $vars);
    
    # model
    $this->create_file_from_template("app/models/{$Model}.php",     'resource/model.php',      $vars);
    $this->create_file_from_template("test/unit/test_{$Model}.php", 'resource/test_model.php', $vars);
    $this->create_file_from_template("test/fixtures/{$table}.yml",  'resource/fixture.yml',    $vars);
    
    # migration
    $migration = gmdate('YmdHis').'_create_'.$table;
    $vars = array(
      'Class' => 'Create'.$Model,
      'table' => $table,
    );
    $this->create_directory('db/migrate');
    $this->create_file_from_template("db/migrate/{$migration}.php", 'resource/migration.php', $vars);
    
    # views
    # IMPROVE: Generate views for 'script/generate resource'
    
    # routes
    $routes_contents = file_get_contents(ROOT.'/config/routes.php');
    $pos = strpos($routes_contents, '$map');
    $pos = strpos($routes_contents, "\n", $pos) + 1;
    $routes_a = substr($routes_contents, 0, $pos);
    $routes_b = substr($routes_contents, $pos);
    $routes_contents  = $routes_a."\n\$map->resource('$resource');\n".$routes_b;
    file_put_contents(ROOT.'/config/routes.php', $routes_contents);
  }
}

?>
