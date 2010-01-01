<?php
require_once __DIR__.'/../../unit.php';

class Test_ActiveRecord_Validations extends Misago\Unit\TestCase
{
  function test_validate()
  {
    $this->fixtures('products');
    
    $product = new Product();
    $this->assert_false($product->is_valid());
    
    $product = new Product(1);
    $this->assert_true($product->is_valid());
    
    $product->name = '';
    $this->assert_false($product->is_valid());
    $this->assert_equal($product->errors->on('name'), array(
      "Name can't be empty",
      "Name cannot be blank",
    ));
    
    unset($product->price);
    $this->assert_false($product->is_valid());
    $this->assert_equal($product->errors->on('price'), "Price of product cannot be blank",
      'I18n translated attribute name in error message');
    
    $product = new Product();
    $product->name = 'pwerti';
    $product->price = 0;
    $this->assert_true($product->is_valid());
  }
  
  function test_validate_presence_of()
  {
    $this->fixtures('monitorings');
    $monit = new Monitoring();
    
    $monit = $monit->create(array());
    $this->assert_true($monit->errors->is_invalid('title'), "field is invalid since it's missing");
    $this->assert_equal($monit->errors->on('title'), 'Title cannot be blank', 'generic message');
    
    $monit = $monit->create(array('title' => '  '));
    $this->assert_true($monit->errors->is_invalid('title'), "field is invalid since it's blank");
    $this->assert_false($monit->errors->is_invalid('description'), "field may be blank on creation");
    
    $monit = $monit->update(1, array('description' => '  '));
    $this->assert_true($monit->errors->is_invalid('description'), "field cannot be blank on update");
    $this->assert_equal($monit->errors->on('description'), 'There must be a description.', 'passed message');
    
    $monit = $monit->update(1, array('description' => 'about server1'));
    $this->assert_false($monit->errors->is_invalid('description'), "field isn't blank");
  }
  
  function test_validate_length_of()
  {
    $this->truncate('monitorings');
    $this->fixtures('monitorings');
    $monit = new Monitoring();
    
    $monit = $monit->create(array());
    $this->assert_false($monit->errors->is_invalid('length_string'), "field can be null/blank");
    
    $monit = $monit->create(array('length_string' => str_repeat('A', 30)));
    $this->assert_true($monit->errors->is_invalid('length_string'), "too long");
    
    $monit = $monit->create(array('length_string2' => str_repeat('A', 45)));
    $this->assert_true($monit->errors->is_invalid('length_string2'), "too long");
    
    $monit = $monit->create(array('length_string2' => 'ABC'));
    $this->assert_true($monit->errors->is_invalid('length_string2'), "too short");
    
    
    $monit = $monit->create(array('length_minmax' => 10, 'too_short' => 'too little'));
    $this->assert_true($monit->errors->is_invalid('length_minmax'), "integer is too small");
    $this->assert_equal($monit->errors->on('length_minmax'), "Too small", "custom error message: too short");
    
    $monit = $monit->create(array('length_minmax' => 3209, 'too_long' => 'too big'));
    $this->assert_true($monit->errors->is_invalid('length_minmax'), "integer is too long");
    $this->assert_equal($monit->errors->on('length_minmax'), "Too big", "custom error message: too long");
    
    $monit = $monit->create(array('length_is' => str_repeat('A', 40)));
    $this->assert_false($monit->errors->is_invalid('length_is'), "string is good length");
    
    $monit = $monit->create(array('length_is' => str_repeat('A', 30)));
    $this->assert_true($monit->errors->is_invalid('length_is'), "string is wrong length");
    $this->assert_equal($monit->errors->on('length_is'), "Length is is wrong length", "generic error message: wrong length");
    
    $monit = $monit->create(array('length_is2' => str_repeat('A', 60)));
    $this->assert_equal($monit->errors->on('length_is2'), "Your miss", "custom error message: wrong length");
    
    $monit = $monit->create(array('length_within' => 60));
    $this->assert_false($monit->errors->is_invalid('length_within'), "integer is within boudaries");
    
    $monit = $monit->create(array('length_within' => 120));
    $this->assert_true($monit->errors->is_invalid('length_within'), "integer is over boudaries");
    
    $monit = $monit->create(array('length_within' => 15));
    $this->assert_true($monit->errors->is_invalid('length_within'), "integer is bellow boudaries");
    
    $monit = $monit->create(array('length_date' => '2009-05-01'));
    $this->assert_false($monit->errors->is_invalid('length_date'), "date is between boudaries");
    
    $monit = $monit->create(array('length_date' => '2008-05-01'));
    $this->assert_true($monit->errors->is_invalid('length_date'), "date is below minimum");
    $this->assert_equal($monit->errors->on('length_date'), "Length date is too short", "generic error message: too short");
    
    $monit = $monit->create(array('length_date' => '2010-05-01'));
    $this->assert_true($monit->errors->is_invalid('length_date'), "date is over maximum");
    $this->assert_equal($monit->errors->on('length_date'), "Length date is too long", "generic error message: too long");
  }
  
  function test_validate_inclusion_of()
  {
    $monit = new Monitoring();
    
    $monit = $monit->create(array('inclusion_string' => ''));
    $this->assert_false($monit->errors->is_invalid('inclusion_string'), "field can be null");
    
    $monit = $monit->create(array('inclusion_string' => '  '));
    $this->assert_false($monit->errors->is_invalid('inclusion_string'), "field can be blank");
    
    $monit = $monit->create(array('inclusion_string' => 'azerty'));
    $this->assert_false($monit->errors->is_invalid('inclusion_string'), "string is in inclusion list");
    
    $monit = $monit->create(array('inclusion_string' => 'mwert'));
    $this->assert_true($monit->errors->is_invalid('inclusion_string'), "string isn't in inclusion list");
    $this->assert_equal($monit->errors->on('inclusion_string'), "This is bad.", "custom error message");
    
    $monit = $monit->create(array('inclusion_integer' => 1));
    $this->assert_false($monit->errors->is_invalid('inclusion_integer'), "int is in inclusion list");
    
    $monit = $monit->create(array('inclusion_integer' => 5));
    $this->assert_true($monit->errors->is_invalid('inclusion_integer'), "int is not in inclusion list");
    $this->assert_equal($monit->errors->on('inclusion_integer'), "Inclusion integer is not included in the list", "generic error message");
  }
  
  function test_validate_exclusion_of()
  {
    $monit = new Monitoring();
    
    $monit = $monit->create(array('exclusion_string' => ''));
    $this->assert_false($monit->errors->is_invalid('exclusion_string'), "field can be null");
    
    $monit = $monit->create(array('exclusion_string' => '  '));
    $this->assert_false($monit->errors->is_invalid('exclusion_string'), "field can be blank");
    
    $monit = $monit->create(array('exclusion_string' => 'mwert'));
    $this->assert_false($monit->errors->is_invalid('exclusion_string'), "string isn't in exclusion list");
    
    $monit = $monit->create(array('exclusion_string' => 'azerty'));
    $this->assert_true($monit->errors->is_invalid('exclusion_string'), "string is in exclusion list");
    $this->assert_equal($monit->errors->on('exclusion_string'), "This is bad.", "custom error message");
    
    $monit = $monit->create(array('exclusion_integer' => 5));
    $this->assert_false($monit->errors->is_invalid('exclusion_integer'), "int is not in exclusion list");
    
    $monit = $monit->create(array('exclusion_integer' => 1));
    $this->assert_true($monit->errors->is_invalid('exclusion_integer'), "int is in exclusion list");
    $this->assert_equal($monit->errors->on('exclusion_integer'), "Exclusion integer's value is reserved", "generic error message");
  }
  
  function test_validate_format_of()
  {
    $monit = new Monitoring();
    
    $monit = $monit->create(array('email' => ''));
    $this->assert_true($monit->errors->is_invalid('email'), "field can't be null (as defined in DB)");
    
    $monit = $monit->create(array('email' => '  '));
    $this->assert_true($monit->errors->is_invalid('email'), "field can't be blank");
    
    $monit = $monit->create(array('email2' => ''));
    $this->assert_false($monit->errors->is_invalid('email2'), "field can be null");
    
    $monit = $monit->create(array('email2' => '  '));
    $this->assert_false($monit->errors->is_invalid('email2'), "field can be blank");
    
    $monit = $monit->create(array('email' => 'toto@toto.com'));
    $this->assert_false($monit->errors->is_invalid('email'), "email is ok");
    
    $monit = $monit->create(array('email' => 'toto.com'));
    $this->assert_true($monit->errors->is_invalid('email'), "email is wrong");
    $this->assert_equal($monit->errors->on('email'), "Email is invalid", "generic error message");
    
    $monit = $monit->create(array('email2' => 'toto.com'));
    $this->assert_equal($monit->errors->on('email2'), "Bad email.", "custom error message");
  }
  
  function test_validate_uniqueness_of()
  {
		$monit = new Monitoring(array('title' => 'server456'));
    $monit->is_valid();
    $this->assert_false($monit->errors->is_invalid('title'), "no duplicate title");
    
    $monit = $monit->create(array('title' => 'server1'));
    $this->assert_true($monit->errors->is_invalid('title'), "duplicate title");
    $this->assert_equal($monit->errors->on('title'), 'Title is already taken', "generic message");
    
    $monit = $monit->create(array('email' => 'root@server3.net'));
    $this->assert_true($monit->errors->is_invalid('email'), "duplicate email");
    $this->assert_equal($monit->errors->on('email'), 'Too late.', "customized message");
  }
}

?>
