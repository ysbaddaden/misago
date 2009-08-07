<?php
$_SERVER['migrate_debug'] = 0;

# IMPROVE: start and stop a test server (script/server -p 3009 -e test -d 0)
class Unit_TestCase extends Unit_Test
{
  static public  $batch_run = false;
  static private $_db;
  
  protected $fixtures = array();
  
  
  function __construct()
  {
    self::create_database();
#    $this->load_fixtures();
    
    parent::__construct();
    
    $this->truncate_fixtures();
    self::drop_database();
  }
  
#  function load_fixtures()
#  {
#    
#  }
  
  function truncate_fixtures()
  {
    $db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    foreach(array_unique($this->fixtures) as $table_name) {
      $db->truncate($table_name);
    }
  }
  
  static function create_database($force=false)
  {
    if (!self::$batch_run or $force)
    {
      self::$_db = ActiveRecord_Connection::create($_SERVER['MISAGO_ENV']);
      self::$_db->connect();

      $dbname = self::$_db->config('database');

      # drops database (just in case)
      if (self::$_db->database_exists($dbname)) {
        self::$_db->drop_database($dbname);
      }
      
      # creates database & tables
      self::$_db->create_database($dbname);
      require MISAGO."/lib/commands/db/migrate.php";
    }
  }
  
  static function drop_database($force=false)
  {
    if (!self::$batch_run or $force)
    {
      ActiveRecord_Connection::get($_SERVER['MISAGO_ENV'])->disconnect();
      self::$_db->drop_database(self::$_db->config('database'));
    }
  }
  
  # Loads one or many fixtures into the database.
  # 
  #   $this->fixtures('chapters,pages');
  function fixtures($fixtures)
  {
    $db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    
    if (!empty($fixtures)) {
      $fixtures = array_collection($fixtures);
    }
    $this->truncate($fixtures);
    
    foreach($fixtures as $fixture)
    {
      $contents = file_get_contents(ROOT."/test/fixtures/$fixture.yml");
      $entries  = Yaml::decode($contents);
      
      foreach($entries as $entry) {
        $db->insert($fixture, $entry);
      }
      
      $this->fixtures[] = $fixture;
    }
  }
  
  # Truncates one or many tables in database.
  # 
  #   $this->truncate('chapters,pages');
  function truncate($tables)
  {
    $db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    
    if (!empty($tables)) {
      $tables = array_collection($tables);
    }
    foreach($tables as $table_name) {
      $db->truncate($table_name);
    }
  }
  
  
  protected function assert_redirect($comment, $rs, $url)
  {
    $this->assert_equal($comment, $rs['headers']['location'], $url);
  }
  
  protected function assert_no_redirect($comment, $rs)
  {
    $this->assert_false($comment, isset($rs['headers']['location']));
  }
  
  protected function assert_status($comment, $rs, $status)
  {
    $this->assert_equal($comment, $rs['status'], $status);
  }
  
  protected function assert_cookie($comment, $rs, $cookie)
  {
    $this->assert_true($comment, isset($rs['headers']['cookies'][$cookie]));
  }
  
  protected function assert_no_cookie($comment, $rs, $cookie)
  {
    $this->assert_false($comment, isset($rs['headers']['cookies'][$cookie]));
  }
  
  # TODO: Move flatten_postfields() to HTTP.
  # TODO: Distinguish between arrays and hashes.
  private function & flatten_postfields($ary, $key=null)
  {
    $a = array();
    foreach($ary as $k => $v)
    {
      if (is_array($v))
      {
        if ($key === null) {
          $b = array_to_postfields($v, $k);
        }
        else {
          $b = array_to_postfields($v, "{$key}[$k]");
        }
        $a = array_merge($a, $b);
      }
      elseif ($key === null) {
        $a[] = urlencode($k).'='.urlencode($v);
      }
      else {
        $a[] = urlencode("{$key}[$k]").'='.urlencode($v);
      }
    }
    return $a;
  }
  
  protected function run_action($method, $uri, $post=null)
  {
    # requests a page
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, "http://localhost:3009$uri");
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
        if (!empty($post))
        {
          $postfields = $this->flatten_postfields($post);
          curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $postfields));
#          curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        break;
      
      case 'PUT':
      case 'DELETE':
        $params['_method'] = $method;
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($post))
        {
          $postfields = $this->flatten_postfields($post);
          curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $postfields));
#          curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        break;
    }
    
    # executes the request
    $output = curl_exec($ch);
    
    if ($output === false) {
      die("\nERROR: please start a test server:\nMISAGO_DEBUG=0 script/server -e test -p 3009\n\n");
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
    $infos  = array(
      'url'     => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
      'status'  => curl_getinfo($ch, CURLINFO_HTTP_CODE),
      'headers' => $headers,
      'body'    => trim(substr($output, curl_getinfo($ch, CURLINFO_HEADER_SIZE))),
    );
    
    curl_close($ch);
    return $infos;
  }
}

?>
