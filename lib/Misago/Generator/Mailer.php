<?php
namespace Misago\Generator;
use Misago\ActiveSupport\String;

class Mailer extends Base
{
  function __construct($args, $options=array())
  {
    if (empty($args))
    {
      echo "Syntax error: script/generate mailer <name>\n";
      exit;
    }
    $this->options = $options;
    
    $Class     = String::camelize($args[0]);
    $view_path = String::underscore($class);
    
    $vars = array(
      'Class'     => $Class,
      'filename'  => $filename,
      'view_path' => $view_path,
    );
    
    $this->create_directory('app/models');
    $this->create_directory('app/views/'.String::underscore($filename));
    $this->create_directory('test/unit');
    
    $this->create_file_from_template("app/models/{$Class}.php",     'mailer/model.php', $vars);
    $this->create_file_from_template("test/unit/test_{$Class}.php", 'mailer/test.php',  $vars);
  }
}

?>
