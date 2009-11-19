<?php
namespace Misago\Generator;

class Controller extends Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate controller <name>\n";
      exit;
    }
    $this->options = $options;
    
    $Class     = String::camelize($args[0]);
    $view_path = String::underscore($filename);
    
    $vars = array(
      'Class'     => $Class,
      'view_path' => $view_path,
    );
    
    $this->create_directory('app/controllers');
    $this->create_directory('app/helpers');
    $this->create_directory("app/views/".$view_path);
#    $this->create_directory("app/views/layouts");
    $this->create_directory("test/functional");
    
    $this->create_file_from_template("app/controllers/{$Class}Controller.php",     'controller/controller.php',  $vars);
    $this->create_file_from_template("test/functional/Test{$Class}Controller.php", 'controller/test.php',        $vars);
    $this->create_file_from_template("app/helpers/{$Class}Helper.php",             'controller/helper.php',      $vars);
#    $this->create_file_from_template("app/views/layouts/{$view_path}.html.tpl",    'controller/layout.html.tpl', $vars);
  }
}

?>
