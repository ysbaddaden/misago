<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class Test_String extends Unit_Test
{
  function test_is_symbol()
  {
    $this->assert_true(is_symbol(":first"));
    $this->assert_true(is_symbol(":all"));
    $this->assert_false(is_symbol("all"));
    $this->assert_false(is_symbol(" :all"));
    
    $this->assert_false(is_symbol("all"));
    $this->assert_false(is_symbol(45));
    $this->assert_false(is_symbol(array()));
    $this->assert_false(is_symbol(new stdClass()));
  }
  
  function test_camelize()
  {
    $this->assert_equal(String::camelize('application_controller'), 'ApplicationController');
    $this->assert_equal(String::camelize('ApplicationController'),  'ApplicationController');
    $this->assert_equal(String::camelize('applicationController'),  'ApplicationController');
  }
  
  function test_underscore()
  {
    $this->assert_equal(String::underscore('application_controller'), 'application_controller');
    $this->assert_equal(String::underscore('ApplicationController'),  'application_controller');
    $this->assert_equal(String::underscore('applicationController'),  'application_controller');
  }
  
  function test_variablize()
  {
    $this->assert_equal(String::variablize('application_controller'), 'applicationController');
    $this->assert_equal(String::variablize('ApplicationController'),  'applicationController');
    $this->assert_equal(String::variablize('applicationController'),  'applicationController');
  }
  
  function test_singularize()
  {
    $this->assert_equal(String::singularize('products'), 'product');
    $this->assert_equal(String::singularize('Products'), 'Product');
  }
  
  function test_pluralize()
  {
    $this->assert_equal(String::pluralize('product'), 'products');
    $this->assert_equal(String::pluralize('Product'), 'Products');
  }
  
  function test_slug()
  {
    $this->assert_equal(String::slug('This is a Test'), 'this-is-a-test');
    $this->assert_equal(String::slug('This is a f*** test!'), 'this-is-a-f-test');
    $this->assert_equal(String::slug("J'ai été à la maison."), "j-ai-été-à-la-maison");
  }
  
  function test_humanize()
  {
    $this->assert_equal(String::humanize('test'), 'Test');
    $this->assert_equal(String::humanize('test_field'), 'Test field');
    $this->assert_equal(String::humanize('UserAgreement'), 'User agreement');
    $this->assert_equal(String::humanize('User Agreement'), 'User agreement');
    $this->assert_equal(String::humanize('User test'), 'User test');
    $this->assert_equal(String::humanize('author_id'), 'Author');
  }
}

new Test_String();

?>
