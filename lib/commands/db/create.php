<?php

$db = null;

try {
  $db = ActiveRecord_Connection::create($_ENV['MISAGO_ENV']);
}
catch(ActiveRecord_Exception $e)
{
  if ($e->getCode() != ActiveRecord_Exception::CantSelectDatabase) {
    throw $e;
  }
}

$database = $db->config('database');
if ($db->create_database($database, array('charset' => 'utf8'))) {
  echo "Created database $database.\n";
}

?>
