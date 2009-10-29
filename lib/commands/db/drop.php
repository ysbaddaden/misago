<?php
echo "DEPRECATED: use 'pake db:drop' instead";

$db = ActiveRecord_Connection::create($_SERVER['MISAGO_ENV']);
$db->connect();

# database name
$dbname = $db->config('database');

if ($db->drop_database($dbname)) {
  echo "Destroyed database $dbname.\n";
}
else {
  throw new Exception("Database $dbname wasn't destroyed.");
}

?>
