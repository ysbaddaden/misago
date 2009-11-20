<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../../config/boot.php';

class Test_#{Class} extends Misago\Unit\TestCase
{
  function test_true()
  {
    $this->assert_true(true);
  }
}

new Test_#{Class}();

?>
