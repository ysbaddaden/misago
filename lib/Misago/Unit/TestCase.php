<?php
namespace Misago\Unit;
use Misago\ActiveRecord;
use Misago\Fixtures;

$_SERVER['migrate_debug'] = 0;

class TestCase extends Assertions\ResponseAssertions
{
  static public  $batch_run = false;
  static private $connection;
  
  protected $fixtures = ':all';
  
  protected function run_tests()
  {
    self::create_database();
    $this->load_fixtures();
    
    parent::run_tests();
    
    $this->truncate($this->fixtures);
    self::drop_database();
  }
  
  protected function run_test($method)
  {
    self::$connection->transaction('begin');
    parent::run_test($method);
    self::$connection->transaction('rollback');
  }
  
  static function create_database($force=false)
  {
    if (!self::$batch_run or $force)
    {
      self::$connection = ActiveRecord\Connection::create($_SERVER['MISAGO_ENV']);
      self::$connection->connect();
      
      $dbname = self::$connection->config('database');
      
      # drops database if exists
      if (self::$connection->database_exists($dbname)) {
        self::$connection->drop_database($dbname);
      }
      
      # creates database & migrates
      self::$connection->create_database($dbname);
      passthru('PAKE_HOME='.ROOT.' MISAGO_ENV='.$_SERVER['MISAGO_ENV'].' pake db:migrate 2>&1 1>/dev/null');
    }
  }
  
  static function drop_database($force=false)
  {
    if (!self::$batch_run or $force)
    {
      ActiveRecord\Connection::get($_SERVER['MISAGO_ENV'])->disconnect();
      self::$connection->drop_database(self::$connection->config('database'));
    }
  }
  
  protected function load_fixtures()
  {
    if ($this->fixtures === ':all') {
      $this->fixtures = Fixtures::all();
    }
    Fixtures::insert($this->fixtures);
  }
  
  # Loads one or many fixtures into the database.
  # 
  #   $this->fixtures('chapters', 'pages');
  function fixtures($fixture)
  {
    $fixtures = func_get_args();
    
    if (!empty($fixtures))
    {
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
