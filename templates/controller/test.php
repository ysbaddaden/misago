<?php

$location = dirname(__FILE__).'/../..';
require_once "$location/lib/misago/lib/unit_test.php";
require_once "$location/app/controllers/#{filename}_controller.php";

class Test_#{Class}Controller extends Unit_Test
{
  function test_true()
  {
    $this->assert_true('true', true);
  }
}

new Test_#{Class}Controller();

?>
