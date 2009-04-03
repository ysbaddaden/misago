<?php
# @package Unit
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
}

?>
