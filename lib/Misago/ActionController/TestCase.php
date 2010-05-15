<?php
namespace Misago\ActionController;
use Misago\Terminal;

$_SERVER['migrate_debug'] = 0;

# Functional tests for ActionControllers.
# 
#   <\?php
#   require __DIR__.'/../test_helper.php';
#   
#   class Test_StoriesController extends Misago\ActionController\TestCase
#   {
#     function test_stories_on_index_page()
#     {
#       $this->get(stories_path());
#       $this->assert_response(200);
#       $this->assert_select('.stories li', 4);
#     }
#   }
#   ?\>
# 
abstract class TestCase extends \Misago\Unit\TestCase
{
  protected $use_transactional_fixtures = false;
  
  # Executes a GET HTTP request on test server.
  # 
  #   $this->get('/accounts');
  #   $this->get(new_account_path());
  # 
  function get($uri) {
    $this->run_action('GET', $uri);
  }
  
  # Executes a POST HTTP request on test server.
  # 
  #   $post = array('account' => array('user_name' => 'azeroth'));
  #   $this->post('/accounts', $post);
  # 
  function post($uri, $postfields=array(), $files=array()) {
    $this->run_action('POST', $uri, $postfields, $files);
  }
  
  # Executes a PUT HTTP request on test server.
  # 
  #   $post = array('account' => array('user_name' => 'azeroth'));
  #   $this->post('/accounts', $post);
  #   
  #   $post  = array('account' => array('user_name' => 'azeroth'));
  #   $files = array('avatar' => '/path/to/avatar.jpg');
  #   $this->post('/accounts', $post);
  # 
  function put($uri, $postfields=array(), $files=array()) {
    $this->run_action('PUT', $uri, $postfields, $files);
  }
  
  # Executes a DELETE HTTP request on test server.
  # 
  #   $this->delete(account_path());
  # 
  function delete($uri) {
    $this->run_action('DELETE', $uri);
  }
  
  # :nodoc:
  function run_action($method, $uri=null, $postfields=null, $files=null)
  {
    $args = func_get_args();
    if (is_object($args[0]))
    {
      $uri        = (string)$args[0];
      $method     = $args[0]->method;
      $postfields = isset($args[1]) ? $args[1] : null;
      $files      = isset($args[2]) ? $args[2] : null;
    }
    
    # requests a page
    $ch = curl_init();
    
    if (strpos($uri, '://') === false) {
      curl_setopt($ch, CURLOPT_URL, "http://localhost:3009$uri");
    }
    else {
      curl_setopt($ch, CURLOPT_URL, $uri);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "cURL");
    
    switch($method)
    {
      case 'GET':
        curl_setopt($ch, CURLOPT_HTTPGET, true);
      break;
      
      case 'POST':
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($postfields)) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, $this->flatten_postfields($postfields));
        }
      break;
      
      case 'PUT':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if (!empty($postfields)) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, $this->flatten_postfields($postfields));
        }
      break;
      
      case 'DELETE':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      break;
    }
    
    # executes the request
    $output = curl_exec($ch);
    
    if ($output === false)
    {
      echo "\n".Terminal::colorize('cURL error:', 'LIGHT_RED').' '.Terminal::colorize(curl_error($ch), 'RED ')."\n\n";
      die("Maybe you didn't started a test server?\n\$ script/server -e test -p 3009\n\n");
    }
    
    # parses headers
    $headers = array('cookies' => array());
    foreach(explode("\n", trim(substr($output, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE)))) as $line)
    {
      if (strpos($line, ':'))
      {
        list($header, $value) = explode(':', trim($line), 2);
        $header = strtolower($header);
        if ($header == 'set-cookie')
        {
          preg_match('/^\s*([^=]+)=([^;]+)/', $value, $match);
          $headers['cookies'][$match[1]] = $match[2];
        }
        $headers[$header] = trim($value);
      }
    }
    
    # gets additional informations
    $this->response = array(
      'url'        => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
      'method'     => $method,
      'postfields' => $postfields,
      'status'     => curl_getinfo($ch, CURLINFO_HTTP_CODE),
      'headers'    => $headers,
      'body'       => trim(substr($output, curl_getinfo($ch, CURLINFO_HEADER_SIZE))),
    );
    
    curl_close($ch);
    return $this->response;
  }
  
  private function flatten_postfields($postfields)
  {
    $data   = array();
    $fields = explode('&', http_build_query($postfields));
    
    foreach($fields as $v)
    {
      if (!empty($v))
      {
        list($k, $v) = explode('=', $v, 2);
        $data[urldecode($k)] = urldecode($v);
      }
    }
    return $data;
  }
  
#  private function build_multipart_form_data($postfields)
#  {
#    $EOF      = "\r\n";
#    $boundary = "---------------------------".sha1(rand(0, 32000));
#    $parts    = array();
#    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
#    
#    $postfields = $this->flatten_postfields($postfields);
#    foreach($postfields as $name => $value)
#    {
#      $part = '';
#      if (strpos($value, '@') !== 0)
#      {
#        $part .= 'Content-Disposition: form-data; name="'.$name.'"'.$EOF.$EOF;
#        $part .= $value.$EOF;
#      }
#      else
#      {
#        $value = substr($value, 1);
#        $part .= 'Content-Disposition: form-data; name="'.$name.'" filename="'.basename($value).'"'.$EOF;
#        $part .= "Content-Type: ".finfo_file($finfo, $value).$EOF.$EOF;
#        $part .= file_get_contents($value).$EOF;
#      }
#      $parts[] = $part;
#    }
#    finfo_close($finfo);
#    
#    $data  = "--$boundary$EOF";
#    $data .= implode("--$boundary$EOF", $parts);
#    $data .= "--$boundary--$EOF";
#    
#    var_dump($data);
#    
#    return array($boundary, $data);
#  }
}

?>
