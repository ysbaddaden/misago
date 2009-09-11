<?php
if (!isset($_ENV['MISAGO_ENV'])) $_ENV['MISAGO_ENV'] = 'test';
require_once dirname(__FILE__).'/../test_app/config/boot.php';

class TestIndexController extends Unit_FunctionalTest
{
  function test_query_string_parse()
  {
    $this->run_action('GET', '/index/get?a=b&c=aze%20rty');
    $this->assert_equal('GET', trim($this->last_action['body']), trim(print_r(array('a' => 'b', 'c' => 'aze rty'), true)));
    
    $this->run_action('POST', '/index/get?a=abcd&c=aze%20rty', array('a' => 'b', 'c' => 'd'));
    $this->assert_equal('POST', trim($this->last_action['body']), trim(print_r(array('a' => 'abcd', 'c' => 'aze rty'), true)));
  }
  
  function test_postfields_parse()
  {
    $this->run_action('POST', '/index/post', array('a' => 'b', 'c' => 'aze rty'));
    $this->assert_equal('POST', trim($this->last_action['body']), trim(print_r(array('a' => 'b', 'c' => 'aze rty'), true)));
    
    $this->run_action('PUT', '/index/post', array('a' => 'testé', 'c' => 'aze rty'));
    $this->assert_equal('PUT with POST data', trim($this->last_action['body']), trim(print_r(array('a' => 'testé', 'c' => 'aze rty'), true)));
  }
  
  function test_postfields_as_deep_array()
  {
    $this->run_action('POST', '/index/post', array('a' => array('b', 'd'), 'c' => 'aze rty'));
    $this->assert_equal('sub array with no keys', trim($this->last_action['body']), trim(print_r(array('a' => array('b', 'd'), 'c' => 'aze rty'), true)));
    
    $this->run_action('POST', '/index/post', array('a' => array('b' => 'bb', 'd' => 'dd'), 'c' => 'aze rty'));
    $this->assert_equal('sub array with keys', trim($this->last_action['body']), trim(print_r(array('a' => array('b' => 'bb', 'd' => 'dd'), 'c' => 'aze rty'), true)));
  }
  
  function test_file_upload()
  {
    $this->run_action('POST', '/index/files', array('myfile' => '@'.__FILE__));
    $this->assert_true('file upload throught POST', (bool)preg_match('/\[name\] => '.basename(__FILE__).'/', $this->last_action['body']));
    
    $this->run_action('PUT', '/index/files', array('myfile' => '@'.__FILE__));
    $this->assert_true('file upload throught PUT', (bool)preg_match('/\[name\] => '.basename(__FILE__).'/', $this->last_action['body']));
  }
}
new TestIndexController();

?>
