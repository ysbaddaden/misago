<?php

class Generator_Plugin extends Generator_Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate plugin <name>\n";
      exit;
    }
    $this->options = $options;
    
    $name = String::underscore($args[0]);
    $vars = array(
      'name'  => $name,
      'Class' => String::camelize($name),
    );
    
    $this->create_directory("vendor/plugins");
    $this->create_directory("vendor/plugins/$name");
    
#    $this->create_file_from_template("vendor/plugins/$name/init.php",      'plugin/init.php',     $vars);
#    $this->create_file_from_template("vendor/plugins/$name/install.php",   'plugin/install.php',   $vars);
#    $this->create_file_from_template("vendor/plugins/$name/uninstall.php", 'plugin/uninstall.php', $vars);
    
    $this->create_directory("vendor/plugins/$name/lib");
    $this->create_file_from_template("vendor/plugins/$name/lib/$name.php", 'plugin/lib.php', $vars);
    
    $this->create_directory("vendor/plugins/$name/tasks");
    $this->create_file_from_template("vendor/plugins/$name/tasks/$name.pake", 'plugin/task.pake', $vars);
    
    $this->create_directory("vendor/plugins/$name/test");
    $this->create_file_from_template("vendor/plugins/$name/test/test_$name.php", 'plugin/test.php', $vars);
  }
}

?>
