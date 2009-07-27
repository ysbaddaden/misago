<?php

$location = dirname(__FILE__).'/../../';
$_SERVER['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_Session extends Unit_Test
{
  function test_flash()
  {
    $this->assert_true(true);
  }
}

new Test_Session();

?>
