<?php

$db = ActiveRecord_Connection::create($_ENV['MISAGO_ENV']);
if ($db->create_database($db->config('database'), array('charset' => 'utf8'))) {
 echo "\n";
}

?>
