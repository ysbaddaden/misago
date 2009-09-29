<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class Test_Unit_Assertions_ModelAssertions extends Unit_TestCase
{
  function test_assert_valid()
  {
    $product = new Product(array('name' => 'keyboard', 'price' => 6.0));
    $this->assert_valid($product);
  }
  
  function test_assert_invalid()
  {
    $product = new Product(array('name' => '', 'price' => 6.0));
    $this->assert_invalid($product);
  }
}

new Test_Unit_Assertions_ModelAssertions();

?>
