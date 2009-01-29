<?php

# basic usage
if (!isset($_SERVER['argv'][1]))
{
  $generators = glob(MISAGO.'templates/generators/*.php');
  foreach($generators as $i => $generator) {
    $generators[$i] = str_replace('.php', '', basename($generator));
  }
  
  echo "Usage: script/generate <generator>\n";
  echo "Available generators: ".implode(', ', $generators)."\n";
  exit 1;
}


class Generator_Base
{
  protected function create_directory($path)
  {
    echo "$path ";
    
    if (!file_exists(APP.$path)) {
      echo mkdir(APP.$path, 0755, true) ? "created" : "error!";
    }
    else {
      echo "exists";
    }
    
    echo "\n";
  }
  
  protected function create_file_from_template($path, $template, array $vars=null)
  {
    echo "$path ";
    
    if (!file_exists(APP.$path))
    {
      $content = file_get_contents(MISAGO.'/templates'.$template);
      
      if (!empty($vars))
      {
        $keys   = array();
        $values = array();
        foreach($vars as $k => $v)
        {
          $keys[]   = "#{$k}";
          $values[] = $v;
        }
        $content = str_replace($keys, $values, $content);
      }
      
      file_put_contents(APP.$path, $content);
    }
    else {
      echo "exists";
    }
    
    echo "\n";
  }
}


# runs generator
require MISAGO."templates/generators/{$_SERVER['argv'][1]}.php";

$class = 'Generator_'.Inflector::camelize($generator);
$args  = array_slice($_SERVER['argb'], 2);
new $class($args);

?>
