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
  
  function test_validate_presence_of()
  {
    $this->fixtures('monitorings');
    $monit = new Monitoring();
    
    $monit = $monit->create(array());
    $this->assert_true("field is invalid since it's missing", $monit->errors->is_invalid('title'));
    $this->assert_equal('generic message', $monit->errors->on('title'), 'Title cannot be blank');
    
    $monit = $monit->create(array('title' => '  '));
    $this->assert_true("field is invalid since it's blank", $monit->errors->is_invalid('title'));
    $this->assert_false("field may be blank on creation", $monit->errors->is_invalid('description'));
    
    $monit = $monit->update(1, array('description' => '  '));
    $this->assert_true("field cannot be blank on update", $monit->errors->is_invalid('description'));
    $this->assert_equal('passed message', $monit->errors->on('description'), 'There must be a description.');
    
    $monit = $monit->update(1, array('description' => 'about server1'));
    $this->assert_false("field isn't blank", $monit->errors->is_invalid('description'));
  }
}

new Test_ActiveRecord_Validations();

?>
