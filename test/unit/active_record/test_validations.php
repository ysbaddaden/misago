<?php

$location = dirname(__FILE__).'/../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

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
    $this->assert_equal('I18n translated attribute name in error message',
      $product->errors->on('price'), "Price of product cannot be blank");
    
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
    
    
    $monit = $monit->create(array('length_minmax' => 10, 'too_short' => 'too little'));
    $this->assert_true("integer is too short", $monit->errors->is_invalid('length_minmax'));
    $this->assert_equal("custom error message: too short", $monit->errors->on('length_minmax'), "Too small");
    
    $monit = $monit->create(array('length_minmax' => 3209, 'too_long' => 'too big'));
    $this->assert_true("integer is too long", $monit->errors->is_invalid('length_minmax'));
    $this->assert_equal("custom error message: too long", $monit->errors->on('length_minmax'), "Too big");
    
    
    $monit = $monit->create(array('length_is' => str_repeat('A', 40)));
    $this->assert_false("string is good length", $monit->errors->is_invalid('length_is'));
    
    $monit = $monit->create(array('length_is' => str_repeat('A', 30)));
    $this->assert_true("string is wrong length", $monit->errors->is_invalid('length_is'));
    $this->assert_equal("generic error message: wrong length", $monit->errors->on('length_is'), "Length is is wrong length");
    
    $monit = $monit->create(array('length_is2' => str_repeat('A', 60)));
    $this->assert_equal("custom error message: wrong length", $monit->errors->on('length_is2'), "Your miss");
    
    
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
    $this->assert_equal("generic error message: too short", $monit->errors->on('length_date'), "Length date is too short");
    
    $monit = $monit->create(array('length_date' => '2010-05-01'));
    $this->assert_true("date is over maximum", $monit->errors->is_invalid('length_date'));
    $this->assert_equal("generic error message: too long", $monit->errors->on('length_date'), "Length date is too long");
    
  }
  
  function test_validate_inclusion_of()
  {
    $monit = new Monitoring();
    
    $monit = $monit->create(array('inclusion_string' => ''));
    $this->assert_false("field can be null", $monit->errors->is_invalid('inclusion_string'));
    
    $monit = $monit->create(array('inclusion_string' => '  '));
    $this->assert_false("field can be blank", $monit->errors->is_invalid('inclusion_string'));
    
    $monit = $monit->create(array('inclusion_string' => 'azerty'));
    $this->assert_false("string is in inclusion list", $monit->errors->is_invalid('inclusion_string'));
    
    $monit = $monit->create(array('inclusion_string' => 'mwert'));
    $this->assert_true("string isn't in inclusion list", $monit->errors->is_invalid('inclusion_string'));
    $this->assert_equal("custom error message", $monit->errors->on('inclusion_string'), "This is bad.");
    
    $monit = $monit->create(array('inclusion_integer' => 1));
    $this->assert_false("int is in inclusion list", $monit->errors->is_invalid('inclusion_integer'));
    
    $monit = $monit->create(array('inclusion_integer' => 5));
    $this->assert_true("int is not in inclusion list", $monit->errors->is_invalid('inclusion_integer'));
    $this->assert_equal("generic error message", $monit->errors->on('inclusion_integer'), "Inclusion integer is not included in the list");
  }
  
  function test_validate_exclusion_of()
  {
    $monit = new Monitoring();
    
    $monit = $monit->create(array('exclusion_string' => ''));
    $this->assert_false("field can be null", $monit->errors->is_invalid('exclusion_string'));
    
    $monit = $monit->create(array('exclusion_string' => '  '));
    $this->assert_false("field can be blank", $monit->errors->is_invalid('exclusion_string'));
    
    $monit = $monit->create(array('exclusion_string' => 'mwert'));
    $this->assert_false("string isn't in exclusion list", $monit->errors->is_invalid('exclusion_string'));
    
    $monit = $monit->create(array('exclusion_string' => 'azerty'));
    $this->assert_true("string is in exclusion list", $monit->errors->is_invalid('exclusion_string'));
    $this->assert_equal("custom error message", $monit->errors->on('exclusion_string'), "This is bad.");
    
    $monit = $monit->create(array('exclusion_integer' => 5));
    $this->assert_false("int is not in exclusion list", $monit->errors->is_invalid('exclusion_integer'));
    
    $monit = $monit->create(array('exclusion_integer' => 1));
    $this->assert_true("int is in exclusion list", $monit->errors->is_invalid('exclusion_integer'));
    $this->assert_equal("generic error message", $monit->errors->on('exclusion_integer'), "Exclusion integer's value is reserved");
  }
  
  function test_validate_format_of()
  {
    $monit = new Monitoring();
    
    $monit = $monit->create(array('email' => ''));
    $this->assert_true("field can't be null (as defined in DB)", $monit->errors->is_invalid('email'));
    
    $monit = $monit->create(array('email' => '  '));
    $this->assert_true("field can't be blank", $monit->errors->is_invalid('email'));
    
    $monit = $monit->create(array('email2' => ''));
    $this->assert_false("field can be null", $monit->errors->is_invalid('email2'));
    
    $monit = $monit->create(array('email2' => '  '));
    $this->assert_false("field can be blank", $monit->errors->is_invalid('email2'));
    
    $monit = $monit->create(array('email' => 'toto@toto.com'));
    $this->assert_false("email is ok", $monit->errors->is_invalid('email'));
    
    $monit = $monit->create(array('email' => 'toto.com'));
    $this->assert_true("email is wrong", $monit->errors->is_invalid('email'));
    $this->assert_equal("generic error message", $monit->errors->on('email'), "Email is invalid");
    
    $monit = $monit->create(array('email2' => 'toto.com'));
    $this->assert_equal("custom error message", $monit->errors->on('email2'), "Bad email.");
  }
  
  function test_validate_uniqueness_of()
  {
		$monit = new Monitoring(array('title' => 'server456'));
    $monit->is_valid();
    $this->assert_false("no duplicate title", $monit->errors->is_invalid('title'));
    
    $monit = $monit->create(array('title' => 'server1'));
    $this->assert_true("duplicate title", $monit->errors->is_invalid('title'));
    $this->assert_equal("generic message", $monit->errors->on('title'), 'Title is already taken');
    
    $monit = $monit->create(array('email' => 'root@server3.net'));
    $this->assert_true("duplicate email", $monit->errors->is_invalid('email'));
    $this->assert_equal("customized message", $monit->errors->on('email'), 'Too late.');
  }
}

new Test_ActiveRecord_Validations();

?>
