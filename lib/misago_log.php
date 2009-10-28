<?php

# DEPRECATED: use Logger instead.
function misago_log($message)
{
  $f = fopen(ROOT."/log/{$_SERVER['MISAGO_ENV']}.log", 'a');
  fwrite($f, "$message\n");
  fclose($f);
}

?>
