<?php
if (!isset($_ENV['MISAGO_ENV'])) $_ENV['MISAGO_ENV'] = 'test';
require_once dirname(__FILE__).'/../test_app/config/boot.php';

class TestIndexController extends ActionController_TestCase
{
  function test_query_string_parse()
  {
    $this->run_action('GET', '/index/get?a=b&c=aze%20rty');
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => 'b', 'c' => 'aze rty'), true)));
    
    $this->run_action('POST', '/index/get?a=abcd&c=aze%20rty', array('a' => 'b', 'c' => 'd'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => 'abcd', 'c' => 'aze rty'), true)));
    
    $this->run_action('PUT', '/index/get?a=abcd&c=aze%20rty', array('a' => 'b', 'c' => 'd'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => 'abcd', 'c' => 'aze rty'), true)));
    
    $this->run_action('DELETE', '/index/get?a=abcd&c=aze%20rty', array('a' => 'b', 'c' => 'd'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => 'abcd', 'c' => 'aze rty'), true)));
  }
  
  function test_postfields_parse()
  {
    $this->run_action('POST', '/index/post', array('a' => 'b', 'c' => 'aze rty'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => 'b', 'c' => 'aze rty'), true)));
    
    $this->run_action('PUT', '/index/post', array('a' => 'testé', 'c' => 'aze rty'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => 'testé', 'c' => 'aze rty'), true)), 'PUT with POST data');
  }
  
  function test_postfields_as_deep_array()
  {
    $this->run_action('POST', '/index/post', array('a' => array('b', 'd'), 'c' => 'aze rty'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => array('b', 'd'), 'c' => 'aze rty'), true)), 'sub array with no keys');
    
    $this->run_action('PUT', '/index/post', array('a' => array('b' => 'bb', 'd' => 'dd'), 'c' => 'aze rty'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => array('b' => 'bb', 'd' => 'dd'), 'c' => 'aze rty'), true)), 'sub array with keys');
  }
  
  function test_file_upload()
  {
    $this->run_action('POST', '/index/files', array('myfile' => '@'.__FILE__));
    $this->assert_true((bool)preg_match('/\[name\] => '.basename(__FILE__).'/', $this->response['body']), 'file upload throught POST');
    
    $this->run_action('PUT', '/index/files', array('myfile' => '@'.__FILE__));
    $this->assert_true((bool)preg_match('/\[name\] => '.basename(__FILE__).'/', $this->response['body']), 'file upload throught PUT');
  }
  
  function test_head()
  {
    $this->run_action('GET', '/index/test_head');
    $this->assert_response(410);
    $this->assert_redirected_to('/');
  }
}
new TestIndexController();

?>
