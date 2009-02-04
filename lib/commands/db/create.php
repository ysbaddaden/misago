<?php

$db = ActiveRecord_Connection::create($_ENV['environment']);
$db->create_database($db->config('database'), array('charset' => 'utf8'));

?>
