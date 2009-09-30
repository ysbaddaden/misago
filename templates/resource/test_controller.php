<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../config/boot.php';

class Test_#{Controller}Controller extends ActionController_TestCase
{
  function test_true()
  {
    $this->assert_true(true);
  }
}

new Test_#{Controller}Controller();

?>
