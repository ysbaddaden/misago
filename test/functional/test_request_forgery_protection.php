<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../test_app/config/boot.php';

class TestRequestForgeryProtection extends Misago\ActionController\TestCase
{
  function test_with_no_token()
  {
    $url = array(':controller' => 'request_forgery', ':action' => 'test');
    
    $this->run_action('GET', url_for($url));
    $this->assert_response(200, 'GET requests are not protected');
    
    $this->run_action('POST', url_for($url));
    $this->assert_response(422, 'POST');
    
    $this->run_action('PUT', url_for($url));
    $this->assert_response(422, 'PUT');
    
    $url['_method'] = 'put';
    $this->run_action('POST', url_for($url));
    $this->assert_response(422, '_method=put');
    
    $url['_method'] = 'delete';
    $this->run_action('POST', url_for($url));
    $this->assert_response(422, '_method=delete');
  }
  
  function test_with_invalid_token()
  {
    $url  = array(':controller' => 'request_forgery', ':action' => 'test');
    $post = array('_token' => '1234567890');
    
    $this->run_action('POST', url_for($url), $post);
    $this->assert_response(422, 'POST');
    
    $this->run_action('PUT', url_for($url), $post);
    $this->assert_response(422, 'PUT');
    
    $url['_method'] = 'put';
    $this->run_action('POST', url_for($url), $post);
    $this->assert_response(422, '_method=put');
    
    $url['_method'] = 'delete';
    $this->run_action('POST', url_for($url), $post);
    $this->assert_response(422, '_method=delete');
  }
  
  function test_with_valid_token()
  {
    $this->run_action('GET', url_for(array(':controller' => 'request_forgery',
      ':action' => 'get_token')));
    $session_id = $this->response['headers']['cookies']['session_id'];
    $token = trim($this->response['body']);
    
    $url = array(':controller' => 'request_forgery', ':action' => 'test',
      'session_id' => $session_id);
    $post = array('_token' => $token);
    
    $this->run_action('POST', url_for($url), $post);
    $this->assert_response(200, 'POST');
    
    $this->run_action('PUT', url_for($url), $post);
    $this->assert_response(200, 'PUT');
    
    $url['_method'] = 'put';
    $this->run_action('POST', url_for($url), $post);
    $this->assert_response(200, '_method=put');
    
    $url['_method'] = 'delete';
    $this->run_action('POST', url_for($url), $post);
    $this->assert_response(200, '_method=delete');
  }
}
new TestRequestForgeryProtection();

?>
