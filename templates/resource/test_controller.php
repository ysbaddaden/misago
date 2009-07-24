<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
$location = __DIR__.'/../..';
require_once "$location/config/boot.php";

class Test_#{Controller}Controller extends Unit_TestCase
{
  function test_true()
  {
    $this->assert_true('true', true);
  }
}

new Test_#{Controller}Controller();

?>
