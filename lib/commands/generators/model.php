<?php

# @package Generator
class Generator_Model extends Generator_Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate model <name>\n";
      exit;
    }
    $this->options = $options;
    
    $filename = String::underscore($args[0]);
    $class    = String::camelize($args[0]);
    $table    = String::pluralize($filename);
    
    $vars = array(
      'filename' => $filename,
      'Class'    => $class,
      'table'    => $table,
    );
    
    # directories
    $this->create_directory('app/models');
    $this->create_directory('test/unit');
    $this->create_directory('test/fixtures');
    
    # files
    $test = $this->create_file_from_template("app/models/{$filename}.php", 'model/model.php', &$vars);
    $this->create_file_from_template("test/unit/test_{$filename}.php", 'model/test.php', &$vars);
    $this->create_file_from_template("test/fixtures/{$table}.yml", 'model/fixture.yml', &$vars);
    
    # migrations
    $filename = gmdate('YmdHis').'_create_'.$table;
    $vars = array(
      'filename' => $filename,
      'Class'    => 'Create'.$class,
      'Model'    => $class,
      'table'    => $table,
    );
    $this->create_directory('db/migrate');
    $this->create_file_from_template("db/migrate/{$filename}.php", 'model/migration.php', &$vars);
  }
}

?>
