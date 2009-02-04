<?php

class Generator_Migration extends Generator_Base
{
  function __construct($args)
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate migration <title>\n";
      exit;
    }
    
    $filename = gmdate('YmdHis').'_'.String::underscore($args[0]);
    $class    = String::camelize($args[0]);
    $vars = array(
      'filename' => $filename,
      'Class'    => $class,
    );
    
    $this->create_directory('db/migrate');
    $this->create_file_from_template("db/migrate/{$filename}.php", 'migration/migration.php', &$vars);
  }
}

?>
