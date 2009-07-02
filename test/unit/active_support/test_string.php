<?php

$_ENV['MISAGO_ENV'] = 'test';
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class Test_String extends Unit_Test
{
  function test_is_symbol()
  {
    $this->assert_true('', is_symbol(":first"));
    $this->assert_true('', is_symbol(":all"));
    $this->assert_false('', is_symbol("all"));
    $this->assert_false('', is_symbol(" :all"));
  }
  
  function test_camelize()
  {
    $this->assert_equal('from underscore',  String::camelize('application_controller'), 'ApplicationController');
    $this->assert_equal('from CamelCase',   String::camelize('ApplicationController'),  'ApplicationController');
    $this->assert_equal('from camelBacked', String::camelize('applicationController'),  'ApplicationController');
  }
  
  function test_underscore()
  {
    $this->assert_equal('from underscore',  String::underscore('application_controller'), 'application_controller');
    $this->assert_equal('from CamelCase',   String::underscore('ApplicationController'),  'application_controller');
    $this->assert_equal('from camelBacked', String::underscore('applicationController'),  'application_controller');
  }
  
  function test_variablize()
  {
    $this->assert_equal('from underscore',  String::variablize('application_controller'), 'applicationController');
    $this->assert_equal('from CamelCase',   String::variablize('ApplicationController'),  'applicationController');
    $this->assert_equal('from camelBacked', String::variablize('applicationController'),  'applicationController');
  }
  
  function test_singularize()
  {
    $this->assert_equal('from underscore', String::singularize('products'), 'product');
    $this->assert_equal('from CamelCase',  String::singularize('Products'), 'Product');
  }
  
  function test_pluralize()
  {
    $this->assert_equal('from underscore', String::pluralize('product'), 'products');
    $this->assert_equal('from CamelCase',  String::pluralize('Product'), 'Products');
  }
  
  function test_slug()
  {
    $this->assert_equal('simple', String::slug('This is a Test'), 'this-is-a-test');
    $this->assert_equal('with some non alphanumerical characts', String::slug('This is a f*** test!'), 'this-is-a-f-test');
    $this->assert_equal('with accented characters', String::slug("J'ai été à la maison."), "j-ai-été-à-la-maison");
  }
  
  function test_humanize()
  {
    $this->assert_equal('', String::humanize('test'), 'Test');
    $this->assert_equal('', String::humanize('test_field'), 'Test field');
    $this->assert_equal('', String::humanize('UserAgreement'), 'User agreement');
    $this->assert_equal('', String::humanize('User Agreement'), 'User agreement');
    $this->assert_equal('', String::humanize('User test'), 'User test');

    $this->assert_equal('', String::humanize('author_id'), 'Author');
  }
}

new Test_String();

?>
