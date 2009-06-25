<?php

if (!isset($_ENV['MISAGO_ENV'])) {
  $_ENV['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../config/boot.php';

class Test_#{Class} extends Unit_TestCase
{
  function test_true()
  {
    $this->assert_true('true', true);
  }
}

new Test_#{Class}();

?>
