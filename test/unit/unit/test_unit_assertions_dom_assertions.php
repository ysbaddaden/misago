<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class Test_Unit_Assertions_DomAssertions extends Unit_TestCase
{
  function test_assert_select()
  {
    $this->response['body'] = '<html><head><title>Welcome</title></head><body><input type="password"/><input class="text"/></body></html>';
    
    $this->assert_select('true (implicit)', 'title');
    $this->assert_select('true (explicit)', 'input', true);
    $this->assert_select('false', 'article', false);
    
    $this->assert_select('integer', 'input', 2);
    $this->assert_select('integer', 'input.text', 1);
    $this->assert_select('integer', 'input[type=password]', 1);
    
    $this->assert_select('text', 'title', 'Welcome');
    $this->assert_select('text', 'head title', 'Welcome');
  }
  
  function test_assert_select_with_html5()
  {
    $this->response['body'] = '<!DOCTYPE html><html><body> <section><article>&nbsp;</article></section> <aside></aside></body></html>';
    $this->assert_select('', 'article', 1);
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
