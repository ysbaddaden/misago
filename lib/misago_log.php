<?php

function misago_log($message)
{
  $f = fopen(ROOT."/log/{$_ENV['MISAGO_ENV']}.log", 'a');
  fwrite($f, "$message\n");
  fclose($f);
}

?>
