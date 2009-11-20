<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
$location = dirname(__FILE__).'/../..';
require_once "$location/config/boot.php";

class Test_Error extends Misago\Unit\TestCase
{
  function test_true()
  {
    $this->assert_true('true', true);
  }
}

new Test_Error();

?>
