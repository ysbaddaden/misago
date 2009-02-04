<?php

class Generator_Controller extends Generator_Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate controller <name>\n";
      exit;
    }
    $this->options = $options;
    
    $filename = String::underscore($args[0]);
    $class    = String::camelize($args[0]);
    $vars = array(
      'filename' => $filename,
      'Class'    => $class,
    );
    
    $this->create_directory('app/controllers');
    $this->create_file_from_template("app/controllers/{$filename}_controller.php", 'controller/controller.php', &$vars);
    
    $this->create_directory("test/functional");
    $this->create_file_from_template("test/functional/test_{$filename}_controller.php", 'controller/test.php', &$vars);
    
    $this->create_directory('app/helpers');
    $this->create_file_from_template("app/helpers/{$filename}_helper.php", 'controller/helper.php', &$vars);
    
    $this->create_directory("app/views/{$filename}");
    $this->create_directory("app/views/layouts");
    $this->create_file_from_template("app/views/layouts/{$filename}.html.tpl", 'controller/layout.html.tpl', &$vars);
  }
}

?>
