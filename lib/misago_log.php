<?php

# Logs a message.
function misago_log($message)
{
  $f = fopen(ROOT."/log/{$_SERVER['MISAGO_ENV']}.log", 'a');
  fwrite($f, "$message\n");
  fclose($f);
}

?>
