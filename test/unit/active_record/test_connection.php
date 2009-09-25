<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
$location = dirname(__FILE__).'/../../..';
require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Connection extends Unit_TestCase
{
  function test_create()
  {
    $db = ActiveRecord_Connection::create('production');
    $adapter = $db->config('adapter');
    $klass = "ActiveRecord_ConnectionAdapters_".String::camelize($adapter)."Adapter";
    $this->assert_true($db instanceof $klass);
  }
  
  function test_get()
  {
    $db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    $adapter = $db->config('adapter');
    $klass = "ActiveRecord_ConnectionAdapters_".String::camelize($adapter)."Adapter";
    $this->assert_true($db instanceof $klass);
  }
}

new Test_ActiveRecord_Connection();

?>
