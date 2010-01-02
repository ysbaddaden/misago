<?php
require_once __DIR__.'/../../unit.php';

class Test_Unit_Assertions_ModelAssertions extends Misago\Unit\TestCase
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
