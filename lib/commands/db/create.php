<?php

$db = ActiveRecord_Connection::create($_ENV['MISAGO_ENV']);

$database = $db->config('database');
if ($db->create_database($database, array('charset' => 'utf8'))) {
  echo "Created database $database.\n";
}

?>
