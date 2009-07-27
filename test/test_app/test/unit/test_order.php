<?php

$location = dirname(__FILE__).'/../..';
require_once "$location/lib/misago/lib/unit_test.php";
require_once "$location/config/boot.php";

class Test_Order extends Unit_Test
{
  function test_true()
  {
    $this->assert_true('true', true);
  }
}

new Test_Order();

?>
