<?php

if ($_SERVER['argc'] == 1) {
  die("Syntax error: script/db/fixtures load\n");
}

if ($_SERVER['argv'][1] == 'load') {
  Fixtures::insert();
}

?>
