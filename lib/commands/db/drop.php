<?php

$db = ActiveRecord_Connection::create($_ENV['MISAGO_ENV']);
$db->drop_database($db->config('database'));

?>
