<?php

class Unit_Assertions_ResponseAssertions extends Unit_Assertions_DomAssertions
{
  protected $response = array();
  
  protected function assert_redirected_to($comment, $url)
  {
    $location = isset($this->response['headers']['location']) ?
      $this->response['headers']['location'] : false;
    $this->assert_equal($comment, $location, ($url === false) ? false : (string)$url);
  }
  
  protected function assert_response($comment, $status)
  {
    $this->assert_equal($comment, $this->response['status'], $status);
  }
  
  protected function assert_cookie_presence($comment, $cookie)
  {
    $this->assert_true($comment, isset($this->response['headers']['cookies'][$cookie]));
  }
  
  protected function assert_cookie_not_present($comment, $cookie)
  {
    $this->assert_false($comment, isset($this->response['headers']['cookies'][$cookie]));
  }
  
  protected function assert_cookie_equal($comment, $cookie, $expected)
  {
    $value = isset($this->response['headers']['cookies'][$cookie]) ?
      $this->response['headers']['cookies'][$cookie] : null;
    $this->assert_equal($comment, $value, $expected);
  }
  
  protected function assert_cookie_not_equal($comment, $cookie, $expected)
  {
    $value = isset($this->response['headers']['cookies'][$cookie]) ?
      $this->response['headers']['cookies'][$cookie] : null;
    $this->assert_not_equal($comment, $value, $expected);
  }
  
  protected function test_assert_template()
  {
    
  }
}

?>
