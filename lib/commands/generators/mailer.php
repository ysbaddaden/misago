<?php

# @package Generator
class Generator_Mailer extends Generator_Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate mailer <name>\n";
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
    
    $this->create_directory('app/models');
    $this->create_directory('app/views/'.$filename);
    $this->create_directory('test/unit');
    
    $test = $this->create_file_from_template("app/models/{$filename}.php", 'mailer/model.php', &$vars);
    $this->create_file_from_template("test/unit/test_{$filename}.php", 'mailer/test.php', &$vars);
  }
}

?>
