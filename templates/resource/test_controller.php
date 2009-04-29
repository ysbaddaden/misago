<?php

if (!isset($_ENV['MISAGO_ENV'])) {
  $_ENV['MISAGO_ENV'] = 'test';
}
$location = dirname(__FILE__).'/../..';
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
