<?php

class Generator_Resource extends Generator_Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate resource <name>\n";
      exit;
    }
    $this->options = $options;
    
    $class          = String::camelize(String::pluralize(String::singularize(String::underscore($args[0]))));
    $filename       = String::underscore($class);
    $model          = String::singularize($class);
    $model_filename = String::underscore($model);
    $table          = $filename;
    
    $vars = array(
      'filename'       => $filename,
      'Controller'     => $class,
      'controller'     => String::underscore($class),
      'Model'          => $model,
      'model'          => String::underscore($model),
      'model_plural'   => String::pluralize(String::underscore($model)),
      'model_filename' => $model_filename,
      'table'          => $table,
    );
    
    $this->create_directory('app/controllers');
    $this->create_directory('app/helpers');
    $this->create_directory("app/views/{$filename}");
    $this->create_directory('app/models');
    $this->create_directory('test/functional');
    $this->create_directory('test/unit');
    $this->create_directory('test/fixtures');
    
    # controller
    $this->create_file_from_template("app/controllers/{$filename}_controller.php",      'resource/controller.php', $vars);
    $this->create_file_from_template("test/functional/test_{$filename}_controller.php", 'resource/test_controller.php',       $vars);
    $this->create_file_from_template("app/helpers/{$filename}_helper.php",              'resource/helper.php',     $vars);
    
    # model
    $this->create_file_from_template("app/models/{$model_filename}.php",     'resource/model.php',      $vars);
    $this->create_file_from_template("test/unit/test_{$model_filename}.php", 'resource/test_model.php', $vars);
    $this->create_file_from_template("test/fixtures/{$table}.yml",           'resource/fixture.yml',    $vars);
    
    # migration
    $migration_filename = gmdate('YmdHis').'_create_'.$table;
    $vars = array(
      'filename' => $model_filename,
      'Class'    => 'Create'.$model,
      'table'    => $table,
    );
    $this->create_directory('db/migrate');
    $this->create_file_from_template("db/migrate/{$migration_filename}.php", 'resource/migration.php', $vars);
    
    # views
    # TODO: Generate views for 'script/generate resource'
    
    # routes
    $routes_contents = file_get_contents(ROOT.'/config/routes.php');
    $pos = strpos($routes_contents, '$map');
    $pos = strpos($routes_contents, "\n", $pos) + 1;
    $routes_a = substr($routes_contents, 0, $pos);
    $routes_b = substr($routes_contents, $pos);
    $routes_contents  = $routes_a."\n\$map->resource('$filename');\n".$routes_b;
    file_put_contents(ROOT.'/config/routes.php', $routes_contents);
  }
}

?>
