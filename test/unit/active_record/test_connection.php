<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../test/test_app/config/boot.php';
use Misago\ActiveSupport\String;
use Misago\ActiveRecord;

class Test_ActiveRecord_Connection extends Misago\Unit\TestCase
{
  function test_create()
  {
    $db = ActiveRecord\Connection::create('production');
    $adapter = $db->config('adapter');
    $klass = "Misago\ActiveRecord\ConnectionAdapters\\".String::camelize($adapter)."Adapter";
    $this->assert_true($db instanceof $klass);
  }
  
  function test_get()
  {
    $db = ActiveRecord\Connection::get($_SERVER['MISAGO_ENV']);
    $adapter = $db->config('adapter');
    $klass = "Misago\ActiveRecord\ConnectionAdapters\\".String::camelize($adapter)."Adapter";
    $this->assert_true($db instanceof $klass);
  }
}

new Test_ActiveRecord_Connection();

?>
