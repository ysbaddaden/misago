<?php
namespace Misago\Unit\Assertions;

abstract class ResponseAssertions extends DomAssertions
{
  protected $response = array();
  
  function assert_redirected_to($url, $message='')
  {
    $location = isset($this->response['headers']['location']) ?
      $this->response['headers']['location'] : false;
    $this->assert_equal($location, ($url === false) ? false : (string)$url, $message);
  }
  
  function assert_response($status, $message='') {
    $this->assert_equal($this->response['status'], $status, $message);
  }
  
  function assert_cookie_presence($cookie, $message='') {
    $this->assert_true(isset($this->response['headers']['cookies'][$cookie]), $message);
  }
  
  function assert_cookie_not_present($cookie, $message='') {
    $this->assert_false(isset($this->response['headers']['cookies'][$cookie]), $message);
  }
  
  function assert_cookie_equal($cookie, $expected, $message='') {
    $value = isset($this->response['headers']['cookies'][$cookie]) ?
      $this->response['headers']['cookies'][$cookie] : null;
    $this->assert_equal($value, $expected, $message);
  }
  
  function assert_cookie_not_equal($cookie, $expected, $message='')
  {
    $value = isset($this->response['headers']['cookies'][$cookie]) ?
      $this->response['headers']['cookies'][$cookie] : null;
    $this->assert_not_equal($value, $expected, $message);
  }
  
  # TODO: assert_template
  function assert_template()
  {
    
  }
}

?>
