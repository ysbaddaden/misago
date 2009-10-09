<?php

class Generator_Migration extends Generator_Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate migration <title>\n";
      exit;
    }
    $this->options = $options;
    
    $filename = gmdate('YmdHis').'_'.String::underscore($args[0]);
    $class    = String::camelize(String::singularize($args[0]));
    $vars = array(
      'filename' => $filename,
      'Class'    => $class,
    );
    
    $this->create_directory('db'.DS.'migrate');
    $this->create_file_from_template("db".DS."migrate".DS."{$filename}.php", 'migration'.DS.'migration.php', $vars);
  }
}

?>
