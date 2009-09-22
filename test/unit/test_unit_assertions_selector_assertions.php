<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../test_app/config/boot.php";

class Test_Unit_Assertions_SelectorAssertions extends Unit_Assertions_SelectorAssertions
{
  function test_assert_select()
  {
    $this->last_action['body'] = '<html><head><title>Welcome</title></head><body><input type="password"/><input class="text"/></body></html>';
    
    $this->assert_select('true (implicit)', 'title');
    $this->assert_select('true (explicit)', 'input', true);
    $this->assert_select('false', 'article', false);
    
    $this->assert_select('integer', 'input', 2);
    $this->assert_select('integer', 'input.text', 1);
    $this->assert_select('integer', 'input[type=password]', 1);
    
    $this->assert_select('text', 'title', 'Welcome');
    $this->assert_select('text', 'head title', 'Welcome');
  }
}

new Test_Unit_Assertions_SelectorAssertions();

?>
