<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once __DIR__."/test_app/config/boot.php";
require_once 'Test/Unit.php';
?>
