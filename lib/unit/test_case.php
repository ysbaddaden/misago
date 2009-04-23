<?php

class Unit_TestCase extends Unit_Test
{
  function __construct()
  {
    $location = ROOT;
    
    # db cleanup (just in case)
    exec("MISAGO_ENV={$_ENV['MISAGO_ENV']} $location/script/db/drop");
    
    # db ignition
    exec("MISAGO_ENV={$_ENV['MISAGO_ENV']} $location/script/db/create");
    exec("MISAGO_ENV={$_ENV['MISAGO_ENV']} $location/script/db/migrate");
    
    # runs tests
    parent::__construct();
    
    # db cleanup
    exec("MISAGO_ENV={$_ENV['MISAGO_ENV']} $location/script/db/drop");
  }
  
  
  function fixtures($fixtures)
  {
    $db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    
    if (!empty($fixtures)) {
      $fixtures = array_collection($fixtures);
    }
    
    foreach($fixtures as $fixture)
    {
      $contents = file_get_contents(ROOT."/test/fixtures/$fixture.yml");
      $entries  = Yaml::decode($contents);
      
      foreach($entries as $entry) {
        $db->insert($fixture, $entry);
      }
    }
  }
  
  function truncate($tables)
  {
    $db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    
    if (!empty($tables)) {
      $tables = array_collection($tables);
    }
    
    foreach($tables as $table)
    {
      $table = $db->quote_table($table);
      $db->execute("TRUNCATE $table");
    }
  }
  
  
  // functional tests
  
  protected function assert_http_redirect($comment, $rs, $url)
  {
    $this->assert_equal($comment, $rs['headers']['location'], $url);
  }
  
  protected function assert_http_status($comment, $rs, $status)
  {
    $this->assert_equal($comment, $rs['status'], $status);
  }
  
  protected function run_action($method, $uri, $post=null)
  {
    # TODO: start test server (script/server -p 3009 -e test -d 0)
    
    # requests a page
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $_ENV['MISAGO_URL'].$uri);
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
        if (!empty($post)) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        break;
      
      case 'PUT':
      case 'DELETE':
        $params['_method'] = $method;
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($post)) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        break;
    }
    
    # executes the request
    $output = curl_exec($ch);
    
    if ($output === false)
    {
      die("\nERROR: please start a test server:\nMISAGO_DEBUG=0 script/server -e test -p 3009\n\n");
    }
    
    # parses headers
    $headers = array();
    foreach(explode("\n", trim(substr($output, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE)))) as $line)
    {
      if (strpos($line, ':'))
      {
        list($header, $value) = explode(':', trim($line), 2);
        $headers[strtolower($header)] = trim($value);
      }
    }
    
    # gets additional informations
    $infos  = array(
      'url'     => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
      'status'  => curl_getinfo($ch, CURLINFO_HTTP_CODE),
      'headers' => $headers,
    );
    
    curl_close($ch);
    return $infos;
  }
}

?>
