<?php
$_SERVER['migrate_debug'] = 0;

class Unit_TestCase extends Unit_Assertions_ResponseAssertions
{
  static public  $batch_run = false;
  static private $_db;
  
  protected $fixtures = array();
  
  
  function __construct()
  {
    self::create_database();
#    $this->fixtures();
    
    parent::__construct();
    
    $this->truncate($this->fixtures);
    self::drop_database();
  }
  
  static function create_database($force=false)
  {
    if (!self::$batch_run or $force)
    {
      self::$_db = ActiveRecord_Connection::create($_SERVER['MISAGO_ENV']);
      self::$_db->connect();
      
      $dbname = self::$_db->config('database');
      
      # drops database if exists
      if (self::$_db->database_exists($dbname)) {
        self::$_db->drop_database($dbname);
      }
      
      # creates database & migrates
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
    if (!empty($fixtures))
    {
      $fixtures = array_collection($fixtures);
      Fixtures::insert($fixtures);
      $this->fixtures = array_merge($this->fixtures, $fixtures);
    }
  }
  
  # Truncates one or many tables in database.
  # 
  #   $this->truncate('chapters,pages');
  function truncate($tables)
  {
    if (!empty($tables)) {
      Fixtures::truncate(array_collection($tables));
    }
  }
}

?>
