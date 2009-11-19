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
    
    $Class = String::camelize($args[0]);
    $table = String::pluralize($filename);
    
    $vars = array(
      'Class' => $Class,
      'table' => $table,
    );
    
    # directories
    $this->create_directory('app/models');
    $this->create_directory('test/unit');
    $this->create_directory('test/fixtures');
    
    # files
    $this->create_file_from_template("app/models/{$Class}.php",    'model/model.php',   $vars);
    $this->create_file_from_template("test/unit/Test{$Class}.php", 'model/test.php',    $vars);
    $this->create_file_from_template("test/fixtures/{$table}.yml", 'model/fixture.yml', $vars);
    
    # migrations
    $migration = gmdate('YmdHis').'_create_'.$table;
    $vars = array(
      'Class'    => 'Create'.$Class,
      'Model'    => $Class,
      'table'    => $table,
    );
    $this->create_directory('db/migrate');
    $this->create_file_from_template("db/migrate/{$migration}.php", 'model/migration.php', $vars);
  }
}

?>
