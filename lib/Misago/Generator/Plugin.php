<?php
namespace Misago\Generator;

class Plugin extends Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate plugin <name>\n";
      exit;
    }
    $this->options = $options;
    
    $Class = String::camelize($args[0]);
    $name  = String::underscore($args[0]);
    $vars  = array(
      'Class' => $Class,
    );
    
    $this->create_directory("vendor/plugins");
    $this->create_directory("vendor/plugins/$Class");
    
    $this->create_file_from_template("vendor/plugins/$Class/install.php",   'plugin/install.php',   $vars);
    $this->create_file_from_template("vendor/plugins/$Class/uninstall.php", 'plugin/uninstall.php', $vars);
    
    $this->create_directory("vendor/plugins/$Class/lib");
    $this->create_file_from_template("vendor/plugins/$Class/lib/$Class.php", 'plugin/lib.php', $vars);
    
    $this->create_directory("vendor/plugins/$Class/tasks");
    $this->create_file_from_template("vendor/plugins/$Class/tasks/$name.pake", 'plugin/task.pake', $vars);
    
    $this->create_directory("vendor/plugins/$Class/test");
    $this->create_file_from_template("vendor/plugins/$Class/test/Test$Class.php", 'plugin/test.php', $vars);
  }
}

?>
