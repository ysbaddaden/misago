<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../test_app/config/boot.php";

class Test_Unit_Assertions_ResponseAssertions extends ActionController_TestCase
{
  function test_response()
  {
    $this->run_action('GET', '/index');
    $this->assert_response(200);
    
    $this->run_action('GET', '/index/forbidden');
    $this->assert_response(403);
  }
  
  function test_redirected_to()
  {
    $this->run_action('GET', '/index/forbidden');
    $this->assert_redirected_to(false, 'not redirected');
    
    $this->run_action('GET', '/index/redirected');
    $this->assert_redirected_to('/');
    $this->assert_response(302);
  }
  
  function test_cookie()
  {
    $this->run_action('GET', '/index/cookie');
    $this->assert_cookie_presence('misago');
    $this->assert_cookie_equal('misago', 'azerty');
    $this->assert_cookie_not_equal('misago', 'qwerty');

    $this->assert_cookie_not_present('feather');
    $this->assert_cookie_not_equal('feather', '123', "cookie isn't set, thus not equal");
  }
  
  function test_select()
  {
    $this->run_action('GET', '/index/html');
    $this->assert_select('section', 1);
    $this->assert_select('article', 2);
    $this->assert_select('article.first', 1);
  }
  
  function test_assert_template()
  {
    
  }
}

new Test_Unit_Assertions_ResponseAssertions();

?>
