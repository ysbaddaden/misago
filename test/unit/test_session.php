<?php

$location = dirname(__FILE__).'/../../';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";
/*
class Test_Session extends Unit_Test
{
  function test_initial_use()
  {
    Session::destroy();
    $this->assert_false('framework started', isset($_SESSION));
    
    Session::start();
    $this->assert_true('', isset($_SESSION));
    
    Session::destroy();
    $this->assert_false('', isset($_SESSION));
  }
  
  function test_uninitialized_session_id()
  {
    $session_id = Session::start('azerty');
    $this->assert_not_equal($session_id, 'azerty');
    
    Session::destroy('azerty');
  }
}

new Test_Session();
*/
?>
