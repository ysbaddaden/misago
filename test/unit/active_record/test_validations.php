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
  
  function test_validate_length_of()
  {
    $this->truncate('monitorings');
    $this->fixtures('monitorings');
    $monit = new Monitoring();
    
    $monit = $monit->create(array());
    $this->assert_false("field can be null/blank", $monit->errors->is_invalid('length_string'));
    
    
    $monit = $monit->create(array('length_string' => str_repeat('A', 30)));
    $this->assert_true("string is too long", $monit->errors->is_invalid('length_string'));
    
    $monit = $monit->create(array('length_string2' => str_repeat('A', 45)));
    $this->assert_true("string2 is too long", $monit->errors->is_invalid('length_string2'));
    
    $monit = $monit->create(array('length_string2' => 'ABC'));
    $this->assert_true("string2 is too short", $monit->errors->is_invalid('length_string2'));
    
    
    $monit = $monit->create(array('length_minmax' => 10));
    $this->assert_true("integer is too short", $monit->errors->is_invalid('length_minmax'));
    
    $monit = $monit->create(array('length_minmax' => 3209));
    $this->assert_true("integer is too long", $monit->errors->is_invalid('length_minmax'));
    
    
    $monit = $monit->create(array('length_is' => str_repeat('A', 40)));
    $this->assert_false("string is good length", $monit->errors->is_invalid('length_is'));
    
    $monit = $monit->create(array('length_is' => str_repeat('A', 30)));
    $this->assert_true("string is wrong length", $monit->errors->is_invalid('length_is'));
    
    
    $monit = $monit->create(array('length_within' => 60));
    $this->assert_false("integer is within boudaries", $monit->errors->is_invalid('length_within'));
    
    $monit = $monit->create(array('length_within' => 120));
    $this->assert_true("integer is over boudaries", $monit->errors->is_invalid('length_within'));
    
    $monit = $monit->create(array('length_within' => 15));
    $this->assert_true("integer is bellow boudaries", $monit->errors->is_invalid('length_within'));
    
    
    $monit = $monit->create(array('length_date' => '2009-05-01'));
    $this->assert_false("date is between boudaries", $monit->errors->is_invalid('length_date'));
    
    $monit = $monit->create(array('length_date' => '2008-05-01'));
    $this->assert_true("date is below minimum", $monit->errors->is_invalid('length_date'));
    
    $monit = $monit->create(array('length_date' => '2010-05-01'));
    $this->assert_true("date is over maximum", $monit->errors->is_invalid('length_date'));
  }
}

new Test_ActiveRecord_Validations();

?>
