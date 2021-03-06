<?php
namespace Misago\Generator;
use Misago\ActiveSupport\String;

class Migration extends Base
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
    
    $this->create_directory('db/migrate');
    $this->create_file_from_template("db/migrate/{$filename}.php", 'migration/migration.php', $vars);
  }
}

?>
