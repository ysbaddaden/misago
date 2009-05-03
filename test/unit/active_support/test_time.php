<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/lib/unit/test.php";
require_once "$location/test/test_app/config/boot.php";

# TODO: Test all is_x() methods
# TODO: Test ago() method
class Test_Time extends Unit_Test
{
  function test_object()
  {
    $time = time();
    $obj  = new Time($time);
    $this->assert_equal('type time', $obj->time, $time);
  }
  
  function test_to_string()
  {
    $obj = new Time('21:52:23', 'time');
    $this->assert_equal('type time', (string)$obj, '09:52:23pm');
    
    $obj = new Time('2009-01-21', 'date');
    $this->assert_equal('type date', (string)$obj, '01/21/2009');
    
    $obj = new Time('2009-01-21 09:52:23', 'datetime');
    $this->assert_equal('type datetime', (string)$obj, '01/21/2009 09:52:23am');
  }
  
  function test_to_s()
  {
    $obj = new Time('21:52:23', 'time');
    $this->assert_equal('type time', $obj->to_s(), '09:52pm');
    
    $obj = new Time('2009-01-21', 'date');
    $this->assert_equal('type date', $obj->to_s(), 'Jan 21');
    
    $obj = new Time('2008-01-09', 'date');
    $this->assert_equal('type date', $obj->to_s(), 'Jan  9 2008');
    
    $obj = new Time('2009-01-21 09:52:23', 'datetime');
    $this->assert_equal('type datetime', $obj->to_s(), 'Jan 21, 09:52am');
    
    $obj = new Time('2008-01-09 09:52:23', 'datetime');
    $this->assert_equal('type datetime', $obj->to_s(), 'Jan  9 2008, 09:52am');
  }
  
  function test_to_query()
  {
    $obj = new Time('21:52:23', 'time');
    $this->assert_equal('type time', $obj->to_query(), '21:52:23');
    
    $obj = new Time('2009-01-21', 'date');
    $this->assert_equal('type date', $obj->to_query(), '2009-01-21');
    
    $obj = new Time('2009-01-21 09:52:23', 'datetime');
    $this->assert_equal('type datetime', $obj->to_query(), '2009-01-21 09:52:23');
  }
}

new Test_Time();

?>
