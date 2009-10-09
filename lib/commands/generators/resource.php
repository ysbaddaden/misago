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
    
    $this->create_directory('app'.DS.'controllers');
    $this->create_directory('app'.DS.'helpers');
    $this->create_directory("app".DS."views".DS."{$filename}");
    $this->create_directory('app'.DS.'models');
    $this->create_directory('test'.DS.'functional');
    $this->create_directory('test'.DS.'unit');
    $this->create_directory('test'.DS.'fixtures');
    
    # controller
    $this->create_file_from_template("app".DS."controllers".DS."{$filename}_controller.php",      'resource'.DS.'controller.php', $vars);
    $this->create_file_from_template("test".DS."functional".DS."test_{$filename}_controller.php", 'resource'.DS.'test_controller.php', $vars);
    $this->create_file_from_template("app".DS."helpers".DS."{$filename}_helper.php",              'resource'.DS.'helper.php', $vars);
    
    # model
    $this->create_file_from_template("app".DS."models".DS."{$model_filename}.php",     'resource'.DS.'model.php',      $vars);
    $this->create_file_from_template("test".DS."unit".DS."test_{$model_filename}.php", 'resource'.DS.'test_model.php', $vars);
    $this->create_file_from_template("test".DS."fixtures".DS."{$table}.yml",           'resource'.DS.'fixture.yml',    $vars);
    
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
    # IMPROVE: Generate views for 'script/generate resource'
    
    # routes
    $routes_contents = file_get_contents(ROOT.DS.'config'.DS.'routes.php');
    $pos = strpos($routes_contents, '$map');
    $pos = strpos($routes_contents, "\n", $pos) + 1;
    $routes_a = substr($routes_contents, 0, $pos);
    $routes_b = substr($routes_contents, $pos);
    $routes_contents  = $routes_a."\n\$map->resource('$filename');\n".$routes_b;
    file_put_contents(ROOT.DS.'config'.DS.'routes.php', $routes_contents);
  }
}

?>
