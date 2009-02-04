<?php

$db = ActiveRecord_Connection::create($_ENV['environment']);
$db->drop_database($db->config('database'));

?>
