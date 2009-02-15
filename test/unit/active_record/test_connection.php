<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Connection extends Unit_Test
{
  function test_load_configuration()
  {
    ActiveRecord_Connection::load_configuration();
    
    $this->assert_equal("", ActiveRecord_Connection::$configurations, array (
      'development' => array(
        'adapter'  => 'mysql',
        'host'     => 'localhost',
        'database' => 'test_app_development',
        'username' => 'root',
        'password' => null,
      ),
      'production' =>  array(
        'adapter'  => 'mysql',
        'host'     => 'localhost',
        'database' => 'test_app_production',
        'username' => 'root',
        'password' => null,
      ),
      'test' => array(
        'adapter'  => 'mysql',
        'host'     => 'localhost',
        'database' => 'test_app_test',
        'username' => 'root',
        'password' => null,
      ),
    ));
  }
  
  function test_create()
  {
    $db = ActiveRecord_Connection::create('production');
    $this->assert_true("", $db instanceof ActiveRecord_ConnectionAdapters_MysqlAdapter);
  }
  
  function test_get()
  {
    $db = ActiveRecord_Connection::get('test');
    $this->assert_true("", $db instanceof ActiveRecord_ConnectionAdapters_MysqlAdapter);
  }
}


system("MISAGO_ENV=test $location/test/test_app/script/db/drop");
system("MISAGO_ENV=test $location/test/test_app/script/db/create");
system("MISAGO_ENV=production $location/test/test_app/script/db/drop");
system("MISAGO_ENV=production $location/test/test_app/script/db/create");

new Test_ActiveRecord_Connection();

system("MISAGO_ENV=test $location/test/test_app/script/db/drop");
system("MISAGO_ENV=production $location/test/test_app/script/db/drop");
?>
