<?php
$_SERVER['migrate_debug'] = 0;

# Functional tests for ActionControllers.
# 
#   <\?php
#   class Test_StoriesController extends ActionControllerTest
#   {
#     function test_index()
#     {
#       $this->run_action(stories_path());
#       $this->assert_response(200);
#       $this->assert_select('.stories li', 4);
#     }
#   }
#   new Test_StoriesController();
#   ?\>
class ActionController_TestCase extends Unit_TestCase
{
  # IMPROVE: Use TestRequest instead of calling a test server.
  function __construct()
  {
    $map = ActionController_Routing::draw();
    $map->build_named_route_helpers();
    parent::__construct();
  }
  
  # Executes an action on test server.
  # 
  #   $this->run_action('GET', '/accounts');
  #   $this->run_action('PUT', '/accounts',
  #     array('account' => array('user_name' => 'azeroth'));
  #   
  #   $this->run_action(new_account_path());
  #   $this->run_action(delete_account_path());
  # 
  protected function & run_action($method, $uri=null, $postfields=null, $files=null)
  {
    $args = func_get_args();
    if (is_object($args[0]))
    {
      $uri        = (string)$args[0];
      $method     = $args[0]->method;
      $postfields = isset($args[1]) ? $args[1] : null;
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
      
      case 'PUT':
        $method = 'POST';
        $postfields['_method'] = 'PUT';
      
      case 'POST':
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($postfields)) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, $this->flatten_postfields($postfields));
        }
      break;
      /*
      case 'PUT':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if (!empty($postfields))
        {
          list($boundary, $data) = $this->build_multipart_form_data($postfields);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=$boundary"));
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          print_r($data);
        }
      break;
      */
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
  
  private function & flatten_postfields(&$postfields)
  {
    $data = array();
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
  /*
  private function build_multipart_form_data(&$postfields)
  {
    $data = "";
    $boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10);
    
    foreach($postfields as $k => $v)
    {
      if (strpos(ltrim($v), '@') === 0)
      {
        $data .= "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"\n"; 
        $data .= "Content-Type: ".mime_content_type($v)."\n"; 
        $data .= "Content-Transfer-Encoding: binary\n\n"; 
        $data .= file_get_contents($v)."\n"; 
        $data .= "--$boundary--\n"; 
      }
      else
      {
        $data .= "--$boundary\n"; 
        $data .= "Content-Disposition: form-data; name=\"".$k."\"\n\n".$v."\n"; 
      }
    }
    $data .= "--$boundary\n"; 
    return array($boundary, $data);
  }
  */
}

?>
