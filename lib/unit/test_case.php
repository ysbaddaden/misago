<?php
$_SERVER['migrate_debug'] = 0;

# IMPROVE: Start and stop a test server (script/server -p 3009 -e test).
# IMPROVE: Write assert_template().
# IMPROVE: Write assert_dom_equal() & assert_dom_not_equal().
# IMPROVE: Write assert_tag() & assert_tag().
class Unit_TestCase extends Unit_Test
{
  static public  $batch_run = false;
  static private $_db;
  
  protected $fixtures    = array();
  protected $last_action;
  
  
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
  
  
  protected function assert_redirected_to($comment, $url)
  {
    $location = isset($this->last_action['headers']['location']) ?
      $this->last_action['headers']['location'] : false;
    $this->assert_equal($comment, $location, $url);
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
      case 'DELETE':
        $postfields['_method'] = $method;
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        $curl_method = 'POST';
        break;
    }
    
    # executes the request
    $output = curl_exec($ch);
    
    if ($output === false)
    {
      echo curl_error($ch)."\n";
      die("\nERROR: please start a test server:\nscript/server -e test -p 3009\n\n");
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
