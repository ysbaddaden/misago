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
    
    $this->create_directory('app'.DS.'controllers');
    $this->create_directory('app'.DS.'helpers');
    $this->create_directory("app".DS."views".DS."{$filename}");
    $this->create_directory("test".DS."functional");
#   $this->create_directory("app".DS."views".DS."layouts");
    
    $this->create_file_from_template("app".DS."controllers".DS."{$filename}_controller.php",      'controller'.DS.'controller.php', $vars);
    $this->create_file_from_template("test".DS."functional".DS."test_{$filename}_controller.php", 'controller'.DS.'test.php',       $vars);
    $this->create_file_from_template("app".DS."helpers".DS."{$filename}_helper.php",              'controller'.DS.'helper.php',     $vars);
    
#   $this->create_file_from_template("app".DS."views".DS."layouts".DS."{$filename}.html.tpl", 'controller'.DS.'layout.html.tpl', $vars);
  }
}

?>
