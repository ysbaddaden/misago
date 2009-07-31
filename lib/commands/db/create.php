<?php

$db = ActiveRecord_Connection::create($_SERVER['MISAGO_ENV']);
$db->connect();

# database name
$dbname = $db->config('database');

if ($db->create_database($dbname, array('charset' => 'utf8'))) {
  echo "Created database $dbname.\n";
}
else {
  throw new Exception("Database $dbname wasn't created.");
}

?>
