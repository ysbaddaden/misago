<?php
require_once __DIR__.'/../unit.php';

class TestIndexController extends Misago\ActionController\TestCase
{
  function test_query_string_parse()
  {
    $this->get('/index/get?a=b&c=aze%20rty');
    $this->assert_equal(trim($this->response['body']),
      trim(print_r(array('a' => 'b', 'c' => 'aze rty'), true)));
    
    $this->post('/index/get?a=abcd&c=aze%20rty', array('a' => 'b', 'c' => 'd'));
    $this->assert_equal(trim($this->response['body']),
      trim(print_r(array('a' => 'abcd', 'c' => 'aze rty'), true)));
    
    $this->put('/index/get?a=abcd&c=aze%20rty', array('a' => 'b', 'c' => 'd'));
    $this->assert_equal(trim($this->response['body']),
      trim(print_r(array('a' => 'abcd', 'c' => 'aze rty'), true)));
    
    $this->delete('/index/get?a=abcd&c=aze%20rty');
    $this->assert_equal(trim($this->response['body']),
      trim(print_r(array('a' => 'abcd', 'c' => 'aze rty'), true)));
  }
  
  function test_postfields_parse()
  {
    $this->post('/index/post', array('a' => 'b', 'c' => 'aze rty'));
    $this->assert_equal(trim($this->response['body']),
      trim(print_r(array('a' => 'b', 'c' => 'aze rty'), true)));
    
    $this->put('/index/post', array('a' => 'testé', 'c' => 'aze rty'));
    $this->assert_equal(trim($this->response['body']),
      trim(print_r(array('a' => 'testé', 'c' => 'aze rty'), true)), 'PUT with POST data');
  }
  
  function test_postfields_as_deep_array()
  {
    $this->post('/index/post', array('a' => array('b', 'd'), 'c' => 'aze rty'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => array('b', 'd'), 'c' => 'aze rty'), true)), 'sub array with no keys');
    
    $this->put('/index/post', array('a' => array('b' => 'bb', 'd' => 'dd'), 'c' => 'aze rty'));
    $this->assert_equal(trim($this->response['body']), trim(print_r(array('a' => array('b' => 'bb', 'd' => 'dd'), 'c' => 'aze rty'), true)), 'sub array with keys');
  }
  
  function test_file_upload()
  {
    $this->post('/index/files', array('myfile' => '@'.__FILE__));
    $this->assert_match('/\[name\] => '.basename(__FILE__).'/', $this->response['body'], 'file upload throught POST');
    
    $this->put('/index/files', array('myfile' => '@'.__FILE__));
    $this->assert_match('/\[name\] => '.basename(__FILE__).'/', $this->response['body'], 'file upload throught PUT');
  }
  
  function test_head()
  {
    $this->get('/index/test_head');
    $this->assert_response(410);
    $this->assert_redirected_to('/');
  }
  
  function test_xml()
  {
    $this->get('/index/errors.xml');
    $this->assert_response(412);
    $this->assert_equal($this->response['body'], '<xml>error</xml>');
  }
}
new TestIndexController();

?>
