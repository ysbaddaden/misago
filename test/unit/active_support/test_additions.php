<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require "$location/test/test_app/config/boot.php";

class Test_Additions extends Unit_Test
{
  function test_is_blank()
  {
    $this->assert_true("empty string", is_blank(''));
    $this->assert_true("space", is_blank(' '));
    $this->assert_true("\\n\\t", is_blank("\n\t"));
    $this->assert_false("", is_blank(" t "));
    $this->assert_false("", is_blank("\nt \n\t"));
  }
}

new Test_Additions();

?>
