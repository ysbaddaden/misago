<?php

$db = ActiveRecord_Connection::create($_SERVER['MISAGO_ENV']);

# database name
$database = $db->config('database');

if ($db->create_database($database, array('charset' => 'utf8'))) {
  echo "Created database $database.\n";
}
else {
  throw new Exception("Database $database wasn't created.");
}

?>
