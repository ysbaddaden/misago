<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Validations extends Unit_TestCase
{
  function test_validate()
  {
    $this->fixtures('products');
    
    $product = new Product();
    $this->assert_false('', $product->is_valid());
    
    $product = new Product(1);
    $this->assert_true('', $product->is_valid());
    
    $product->name = '';
    $this->assert_false('', $product->is_valid());
    $this->assert_equal('', $product->errors->on('name'), array(
      "Name can't be empty",
      "Name cannot be blank",
    ));
    
    unset($product->price);
    $this->assert_false('', $product->is_valid());
    $this->assert_equal('', $product->errors->on('price'), "Price cannot be blank");
    
    $product = new Product();
    $product->name = 'pwerti';
    $product->price = 0;
    $this->assert_true('', $product->is_valid());
  }
}

new Test_ActiveRecord_Validations();

?>
