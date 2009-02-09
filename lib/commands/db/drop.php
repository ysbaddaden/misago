<?php

$db = ActiveRecord_Connection::create($_ENV['MISAGO_ENV']);

$database = $db->config('database');
if ($db->drop_database($database)) {
  echo "Destroyed database $database.\n";
}

?>
