<?php

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
    $this->create_directory('app'.DS.'models');
    $this->create_directory('test'.DS.'unit');
    $this->create_directory('test'.DS.'fixtures');
    
    # files
    $test = $this->create_file_from_template("app".DS."models".DS."{$filename}.php", 'model'.DS.'model.php', $vars);
    $this->create_file_from_template("test".DS."unit".DS."test_{$filename}.php", 'model'.DS.'test.php', $vars);
    $this->create_file_from_template("test".DS."fixtures".DS."{$table}.yml", 'model'.DS.'fixture.yml', $vars);
    
    # migrations
    $filename = gmdate('YmdHis').'_create_'.$table;
    $vars = array(
      'filename' => $filename,
      'Class'    => 'Create'.$class,
      'Model'    => $class,
      'table'    => $table,
    );
    $this->create_directory('db'.DS.'migrate');
    $this->create_file_from_template("db".DS."migrate".DS."{$filename}.php", 'model'.DS.'migration.php', $vars);
  }
}

?>
