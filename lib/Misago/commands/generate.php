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
  $generators = array();
  
  $d = dir(MISAGO.'/lib/commands/generators/');
  while(($file = $d->read()) !== false)
  {
    if (preg_match('/^(.+)\.php$/', $file, $match)) {
      $generators[] = $match[1];
    }
  }
  $d->close();
  
  echo "Usage: script/generate <generator>\n";
  echo "Available generators: ".implode(', ', $generators)."\n";
  exit;
}

# runs generator
$generator = array_shift($arguments);
$class     = '\Misago\Generator\\'.String::camelize($generator);
new $class($arguments, $options);

?>
