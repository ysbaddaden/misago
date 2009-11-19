<?php

# misago's tasks
$d = dir(__DIR__);
while(($f = $d->read()) != false)
{
  if (strpos($f, '.pake')) {
    include $f;
  }
}
$d->close();

# plugins' tasks
$plugins = \Misago\Plugin::list_plugins();
foreach($plugins as $plugin)
{
  $path = ROOT."/vendor/plugins/$plugin/tasks";
  if (is_dir($path))
  {
    $d = dir($path);
    while(($f = $d->read()) != false)
    {
      if (strpos($f, '.pake')) {
        include "$path/$f";
      }
    }
    $d->close();
  }
}

# app's tasks
if (is_dir(ROOT.'/lib/tasks'))
{
  $d = dir(ROOT.'/lib/tasks');
  while(($f = $d->read()) != false)
  {
    if (strpos($f, '.pake')) {
      include ROOT."/lib/tasks/$f";
    }
  }
  $d->close();
}

?>
