<?php

# command line arguments
$arguments = array();
$options   = array();

for($i = 1; $i < count($_SERVER['argv']); $i++)
{
  $arg = $_SERVER['argv'][$i];
  if (strpos($arg, '-') === 0) {
    $options[] = $arg;
  }
  else {
    $arguments[] = $arg;
  }
}


# basic usage
if (!isset($arguments[0]))
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
  protected $options = array();
  
  protected function check_path($path, $type='file')
  {
    if (file_exists(ROOT.'/'.$path))
    {
      if ($type == 'file' and in_array('-f', $this->options)) {
        echo "   overwrite  $path\n";
      }
      else
      {
        echo "      exists  $path\n";
        return false;
      }
    }
    else {
      echo "      create  $path\n";
    }
    return true;
  }
  
  protected function create_directory($path)
  {
    if ($this->check_path("$path/", 'directory')) {
      mkdir(ROOT.'/'.$path, 0755, true);
    }
  }
  
  protected function create_file_from_template($path, $template, array $vars=null)
  {
    if ($this->check_path($path))
    {
      $content = file_get_contents(MISAGO.'/templates/'.$template);
      
      if (!empty($vars))
      {
        $keys   = array();
        $values = array();
        foreach($vars as $k => $v)
        {
          $keys[]   = '#{'.$k.'}';
          $values[] = $v;
        }
        $content = str_replace($keys, $values, $content);
      }
      
      file_put_contents(ROOT.'/'.$path, $content);
    }
  }
}

# runs generator
$generator = array_shift($arguments);
$class     = 'Generator_'.String::camelize($generator);

require "commands/generators/{$generator}.php";
new $class($arguments, $options);

?>
