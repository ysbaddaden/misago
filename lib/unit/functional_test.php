<?php
$_SERVER['migrate_debug'] = 0;

# IMPROVE: Write assert_template().
# IMPROVE: Write assert_dom_equal() & assert_dom_not_equal().
# IMPROVE: Write assert_tag() & assert_not_tag().
class Unit_FunctionalTest extends Unit_TestCase
{
  protected $last_action;
  
  # IMPROVE: Start and stop a test server (script/server -p 3009 -e test).
  function __construct()
  {
    $map = ActionController_Routing::draw();
    $map->build_path_and_url_helpers();
    
    parent::__construct();
  }
  
  protected function assert_redirected_to($comment, $url)
  {
    $location = isset($this->last_action['headers']['location']) ?
      $this->last_action['headers']['location'] : false;
    $this->assert_equal($comment, $location, ($url === false) ? false : (string)$url);
  }
  
  protected function assert_response($comment, $status)
  {
    $this->assert_equal($comment, $this->last_action['status'], $status);
  }
  
  protected function assert_cookie_presence($comment, $cookie)
  {
    $this->assert_true($comment, isset($this->last_action['headers']['cookies'][$cookie]));
  }
  
  protected function assert_cookie_not_present($comment, $cookie)
  {
    $this->assert_false($comment, isset($this->last_action['headers']['cookies'][$cookie]));
  }
  
  protected function assert_cookie_equal($comment, $cookie, $expected)
  {
    $value = isset($this->last_action['headers']['cookies'][$cookie]) ?
      $this->last_action['headers']['cookies'][$cookie] : null;
    $this->assert_equal($comment, $value, $expected);
  }
  
  protected function assert_cookie_not_equal($comment, $cookie, $expected)
  {
    $value = isset($this->last_action['headers']['cookies'][$cookie]) ?
      $this->last_action['headers']['cookies'][$cookie] : null;
    $this->assert_not_equal($comment, $value, $expected);
  }
  
  
  protected function & run_action($method, $uri, $postfields=null)
  {
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
        $curl_method = 'GET';
        break;
      
      case 'POST':
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($postfields)) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        }
        $curl_method = 'POST';
        break;
      
      case 'PUT':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        $curl_method = 'PUT';
        break;
      
      case 'DELETE':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $curl_method = 'DELETE';
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
    $this->last_action = array(
      'url'        => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
      'method'     => $curl_method,
      'postfields' => $postfields,
      'status'     => curl_getinfo($ch, CURLINFO_HTTP_CODE),
      'headers'    => $headers,
      'body'       => trim(substr($output, curl_getinfo($ch, CURLINFO_HEADER_SIZE))),
    );
    
    curl_close($ch);
    return $this->last_action;
  }
}

?>
