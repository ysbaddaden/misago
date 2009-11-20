<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class Test_Unit_Assertions_DomAssertions extends Misago\Unit\TestCase
{
  function test_assert_select()
  {
    $this->response['body'] = '<html><head><title>Welcome</title></head><body><input type="password"/><input class="text"/></body></html>';
    
    $this->assert_select('title');
    $this->assert_select('input', true);
    $this->assert_select('article', false);
    
    $this->assert_select('input', 2);
    $this->assert_select('input.text', 1);
    $this->assert_select('input[type=password]', 1);
    
    $this->assert_select('title', 'Welcome');
    $this->assert_select('head title', 'Welcome');
  }
  
  function test_assert_select_with_html5()
  {
    $this->response['body'] = '<!DOCTYPE html><html><body> <section><article>&nbsp;</article></section> <aside></aside></body></html>';
    $this->assert_select('article', 1);
  }
  
  function test_assert_select_with_classnames()
  {
    $this->response['body'] = '<html><body><article class="female"></article><article class="male"></article></body></html>';
    $this->assert_select('article.male', 1);
    $this->assert_select('article.female', 1);
    
    $this->response['body'] = '<html><body><article class="female"></article><article class="profile male"></article></body></html>';
    $this->assert_select('article.male', 1);
  }
  
  function test_assert_tag()
  {
    
  }
  
  function test_assert_no_tag()
  {
    
  }
  
  function test_assert_dom_equal()
  {
    
  }
  
  function test_assert_dom_not_equal()
  {
    
  }
}

new Test_Unit_Assertions_DomAssertions();

?>
