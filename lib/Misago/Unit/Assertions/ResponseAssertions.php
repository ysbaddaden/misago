<?php

class Unit_Assertions_ResponseAssertions extends Unit_Assertions_DomAssertions
{
  protected $response = array();
  
  protected function assert_redirected_to($url, $comment=null)
  {
    $location = isset($this->response['headers']['location']) ?
      $this->response['headers']['location'] : false;
    $this->assert_equal($location, ($url === false) ? false : (string)$url, $comment);
  }
  
  protected function assert_response($status, $comment=null)
  {
    $this->assert_equal($this->response['status'], $status, $comment);
  }
  
  protected function assert_cookie_presence($cookie, $comment=null)
  {
    $this->assert_true(isset($this->response['headers']['cookies'][$cookie]), $comment);
  }
  
  protected function assert_cookie_not_present($cookie, $comment=null)
  {
    $this->assert_false(isset($this->response['headers']['cookies'][$cookie]), $comment);
  }
  
  protected function assert_cookie_equal($cookie, $expected, $comment=null)
  {
    $value = isset($this->response['headers']['cookies'][$cookie]) ?
      $this->response['headers']['cookies'][$cookie] : null;
    $this->assert_equal($value, $expected, $comment);
  }
  
  protected function assert_cookie_not_equal($cookie, $expected, $comment=null)
  {
    $value = isset($this->response['headers']['cookies'][$cookie]) ?
      $this->response['headers']['cookies'][$cookie] : null;
    $this->assert_not_equal($value, $expected, $comment);
  }
  
  # TODO: assert_template
  protected function assert_template()
  {
    
  }
}

?>
