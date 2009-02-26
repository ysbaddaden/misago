<?php

if (!isset($_ENV['MISAGO_ENV'])) {
  $_ENV['MISAGO_ENV'] = 'test';
}
$location = dirname(__FILE__).'/../..';
require_once "$location/app/controllers/#{filename}_controller.php";

class Test_#{Class}Controller extends Unit_TestCase
{
  function test_true()
  {
    $this->assert_true('true', true);
  }
}

new Test_#{Class}Controller();

?>
