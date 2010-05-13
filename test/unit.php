<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once __DIR__."/test_app/config/boot.php";

$test_controller = new TestController();
$test_controller->process(new Misago\ActionController\TestRequest(array(':controller' => 'test', ':action' => 'index')));

require_once 'Test/Unit.php';
?>
