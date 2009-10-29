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
