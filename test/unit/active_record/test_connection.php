<?php

$_SERVER['MISAGO_ENV'] = 'test';
$location = dirname(__FILE__).'/../../..';
require_once "$location/test/test_app/config/boot.php";

# FIXME: enable test_load_configuration().
class Test_ActiveRecord_Connection extends Unit_TestCase
{
  /*
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
  */
  
  function test_create()
  {
    $db = ActiveRecord_Connection::create('production');
    $adapter = $db->config('adapter');
    $klass = "ActiveRecord_ConnectionAdapters_".String::camelize($adapter)."Adapter";
    $this->assert_true("", $db instanceof $klass);
  }
  
  function test_get()
  {
    $db = ActiveRecord_Connection::get('test');
    $adapter = $db->config('adapter');
    $klass = "ActiveRecord_ConnectionAdapters_".String::camelize($adapter)."Adapter";
    $this->assert_true("", $db instanceof $klass);
  }
}

new Test_ActiveRecord_Connection();

?>
