<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class Test_Additions extends Unit_Test
{
  function test_is_blank()
  {
    $this->assert_true("empty string", is_blank(''));
    $this->assert_true("space", is_blank(' '));
    $this->assert_true("\\n\\t", is_blank("\n\t"));
    $this->assert_false("", is_blank(" t "));
    $this->assert_false("", is_blank("\nt \n\t"));
    $this->assert_false("", is_blank(0));
  }
}

new Test_Additions();

?>
