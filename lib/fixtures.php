<?php

class Fixtures
{
  static private $connection;
  static private $cache = array();
  
  static function initialize()
  {
    self::$connection = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
  }
  
  static private function & all()
  {
    $files = glob(ROOT.'/test/fixtures/*.yml');
    sort($files);
    
    $fixtures = array();
    foreach($files as $file) {
      $fixtures[] = str_replace('.yml', '', basename($file));
    }
    return $fixtures;
  }
  
  static private function & parse($fixture)
  {
    if (!isset(self::$cache[$fixture])) {
      self::$cache[$fixture] = Yaml::decode(file_get_contents(ROOT.'/test/fixtures/'$fixture.'.yml'));
    }
    return self::$cache[$fixture];
  }
  
  static function insert($fixture=null)
  {
    if ($fixture === null or is_array($fixture))
    {
      $fixtures = ($fixture === null) ? self::all() : array_unique($fixture);
      foreach($fixtures as $fixture) {
        self::insert($fixture);
      }
      return;
    }
    self::$connection->truncate($fixture);
    
    $rows = Fixtures::parse($fixture);
    foreach($rows as $row) {
      self::$connection->insert($fixture, $row);
    }
  }
  
  static function truncate($table)
  {
    if (is_array($table))
    {
      foreach($table as $t) {
        self::truncate($t);
      }
      return;
    }
    self::$connection->truncate($table);
  }
}

Fixtures::initialize();
?>
