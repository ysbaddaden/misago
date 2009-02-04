<?php

# basic usage
if (!isset($_SERVER['argv'][1]))
{
  $generators = glob(MISAGO.'/lib/commands/generators/*.php');
  foreach($generators as $i => $generator) {
    $generators[$i] = str_replace('.php', '', basename($generator));
  }
  
  echo "Usage: script/generate <generator>\n";
  echo "Available generators: ".implode(', ', $generators)."\n";
  exit;
}


class Generator_Base
{
  protected function create_directory($path)
  {
    if (!file_exists(ROOT.'/'.$path))
    {
      echo "      create  $path/\n";
      mkdir(ROOT.'/'.$path, 0755, true);
    }
    else {
      echo "      exists  $path/\n";
    }
  }
  
  protected function create_file_from_template($path, $template, array $vars=null)
  {
    if (!file_exists(ROOT.'/'.$path))
    {
      echo "      create  $path\n";
      
      $content = file_get_contents(MISAGO.'/templates/'.$template);
      
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
      
      file_put_contents(ROOT.'/'.$path, $content);
    }
    else {
      echo "      exists  $path\n";
    }
  }
}


# runs generator
$generator = $_SERVER['argv'][1];
$class = 'Generator_'.String::camelize($generator);
$args  = array_slice($_SERVER['argv'], 2);

require "commands/generators/{$generator}.php";
new $class($args);

?>
