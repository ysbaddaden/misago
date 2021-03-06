<?php
require_once __DIR__.'/../../unit.php';

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
  
  function test_assert_dom_equal()
  {
    $this->assert_dom_equal('<p></p>', '<p></p>');
    $this->assert_dom_equal('<p  class="thing"> aez  </p>', '<p class="thing">aez</p>');
    $this->assert_dom_equal('<p  class="thing"> <a>aez<a>  </p>', '<p class="thing"><a>aez</a></p>');
    $this->assert_dom_equal('<p  class="thing"> <a href="http://example.com" class="external">example.com<a>  </p>',
      '<p class="thing"><a class="external" href="http://example.com">example.com</a></p>');
  }
  
  function test_assert_dom_not_equal()
  {
    $this->assert_dom_equal('<p  class="thing"> <a>aez<a>  </p>', '<p class="thing"><a>a e z</a></p>');
  }
}

new Test_Unit_Assertions_DomAssertions();

?>
