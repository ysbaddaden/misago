<?php
require_once __DIR__.'/../../unit.php';

class Test_Additions extends Test\Unit\TestCase
{
  function test_is_blank()
  {
    $this->assert_true(is_blank(''));
    $this->assert_true(is_blank(' '));
    $this->assert_true(is_blank("\n\t"));
    $this->assert_false(is_blank(" t "));
    $this->assert_false(is_blank("\nt \n\t"));
    $this->assert_false(is_blank(0));
  }
}

?>
