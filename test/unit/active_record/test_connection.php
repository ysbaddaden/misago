<?php

$_SERVER['MISAGO_ENV'] = 'test';
$location = dirname(__FILE__).'/../../..';
require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Connection extends Unit_TestCase
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
        'database' => 'test_app',
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

new Test_ActiveRecord_Connection();

?>
