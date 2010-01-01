<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once __DIR__."/../config/boot.php";
require_once 'Test/Unit.php';
?>
