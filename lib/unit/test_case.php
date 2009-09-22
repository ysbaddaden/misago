<?php
$_SERVER['migrate_debug'] = 0;

class Unit_TestCase extends Unit_Assertions_ModelAssertions
{
  static public  $batch_run = false;
  static private $_db;
  
  protected $fixtures = array();
  
  
  function __construct()
  {
    self::create_database();
#    $this->fixtures();
    
    parent::__construct();
    
    $this->truncate_fixtures();
    self::drop_database();
  }
  
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
}

?>
