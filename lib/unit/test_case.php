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
  
  # FIXME: Finish Unit_TestCase::fixtures().
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
}

?>
