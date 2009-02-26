<?php

# IMPROVE: Generate YAML fixtures.
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
    
    $filename       = String::uderscore($args[0]);
    $class          = String::camelize($args[0]);
    $model          = String::singularize($class);
    $model_filename = String::underscore($model);
    $table          = $filename;
    
    $vars = array(
      'filename'       => $filename,
      'Class'          => $class,
      'Model'          => $model,
      'model_filename' => $model_filename,
      'table'          => $table,
    );
    
    $this->create_directory('app/controllers');
    $this->create_directory('app/helpers');
    $this->create_directory("app/views/{$filename}");
    $this->create_directory('app/models');
    $this->create_directory('test/functional');
    $this->create_directory('test/unit');
#   $this->create_directory('test/fixtures');
    
    # controller
    $this->create_file_from_template("app/controllers/{$filename}_controller.php",      'resource/controller.php', &$vars);
    $this->create_file_from_template("test/functional/test_{$filename}_controller.php", 'resource/test.php',       &$vars);
    $this->create_file_from_template("app/helpers/{$filename}_helper.php",              'resource/helper.php',     &$vars);
    
    # model
    $this->create_file_from_template("app/models/{$model_filename}.php",     'resource/model.php',      &$vars);
    $this->create_file_from_template("test/unit/test_{$model_filename}.php", 'resource/test_model.php', &$vars);
#   $this->create_file_from_template("test/fixtures/{$table}.yml",           'resource/fixture.yml',    &$vars);
    
    # migration
    $filename = gmdate('YmdHis').'_create_'.$table;
    $vars = array(
      'filename' => $model_filename,
      'Class'    => 'Create'.$model,
      'table'    => $table,
    );
    $this->create_directory('db/migrate');
    $this->create_file_from_template("db/migrate/{$filename}.php", 'resource/migration.php', &$vars);
    
    # views
    # TODO: Generate views for 'script/generate resource'
    
  }
}

?>
