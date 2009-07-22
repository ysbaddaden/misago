<?php

$location = dirname(__FILE__).'/../../';
$_SERVER['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_Session extends Unit_Test
{
  function test_flash()
  {
    $this->assert_null('no message has been set', Session::flash());
    Session::flash('some stupid message');
    $this->assert_equal('must return message', Session::flash(), '<p id="flash-message">some stupid message</p>');
    $this->assert_null('message has already been retrieved', Session::flash());
  }
}

new Test_Session();

?>
