<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once __DIR__.'/../../config/boot.php';

class Test_#{Class} extends Unit_TestCase
{
  function test_true()
  {
    $this->assert_true('true', true);
  }
}

new Test_#{Class}();

?>
