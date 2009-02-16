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
}

?>
