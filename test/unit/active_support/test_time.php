<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

# TEST: Test Time::ago() method.
class Test_Time extends Unit_Test
{
  function test_toString()
  {
    $obj = new Time('21:52:23', 'time');
    $this->assert_equal('type time', (string)$obj, '21:52:23');
    
    $obj = new Time('2009-01-21', 'date');
    $this->assert_equal('type date', (string)$obj, '2009-01-21');
    
    $obj = new Time('2009-01-21 09:52:23', 'datetime');
    $this->assert_equal('type datetime', (string)$obj, '2009-01-21 09:52:23');
    
    $obj = new Time('', 'datetime');
    $this->assert_equal('blank time', (string)$obj, '');
    
#    $obj = new Time(null, 'datetime');
#    $this->assert_equal('null time is now', (string)$obj, '????'); # how to test this?
  }
  
  function test_magic_attributes()
  {
    $obj = new Time('2009-10-05');
    $this->assert_equal('year', $obj->year, '2009');
    $this->assert_equal('month', $obj->month, '10');
    $this->assert_equal('day', $obj->day, '05');
    
    $obj = new Time('1985-06-30');
    $this->assert_equal('year', $obj->year, '1985');
    $this->assert_equal('month', $obj->month, '06');
    $this->assert_equal('day', $obj->day, '30');
    
    $obj = new Time('1976-01-31 00:45:32');
    $this->assert_equal('year', $obj->year, '1976');
    $this->assert_equal('month', $obj->month, '01');
    $this->assert_equal('day', $obj->day, '31');
    $this->assert_equal('hour', $obj->hour, '00');
    $this->assert_equal('min', $obj->min, '45');
    $this->assert_equal('sec', $obj->sec, '32');
  }
  
  /*
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
  */
  
  function test_is_today()
  {
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_true("today (date)", $date->is_today());
    
    $date = new Time(date('Y-m-d H:i:s'), 'datetime');
    $this->assert_true("today (datetime)", $date->is_today());
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_false("the day before yesterday", $date->is_today());
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_false("tomorrow", $date->is_today());
  }
  
  function test_is_yesterday()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_true("yesterday (date)", $date->is_yesterday());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('-1 day')), 'datetime');
    $this->assert_true("yesterday (datetime)", $date->is_yesterday());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_false("today", $date->is_yesterday());
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_false("the day before yesterday", $date->is_yesterday());
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_false("tomorrow", $date->is_yesterday());
  }
  
  function test_is_past()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_true("yesterday (date)", $date->is_past());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('-1 day')), 'datetime');
    $this->assert_true("yesterday (datetime)", $date->is_past());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_false("today", $date->is_past());
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_true("the day before yesterday", $date->is_past());
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_false("tomorrow", $date->is_past());
  }
  
  function test_is_tomorrow()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_false("yesterday (date)", $date->is_tomorrow());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('-1 day')), 'datetime');
    $this->assert_false("yesterday (datetime)", $date->is_tomorrow());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_false("today", $date->is_tomorrow());
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_false("the day before yesterday", $date->is_tomorrow());
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_true("tomorrow (date)", $date->is_tomorrow());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('+1 day')), 'datetime');
    $this->assert_true("tomorrow (datetime)", $date->is_tomorrow());
  }
  
  function test_is_future()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_false("yesterday (date)", $date->is_future());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('-1 day')), 'datetime');
    $this->assert_false("yesterday (datetime)", $date->is_future());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_false("today", $date->is_future());
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_false("the day before yesterday", $date->is_future());
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_true("tomorrow (date)", $date->is_future());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('+1 day')), 'datetime');
    $this->assert_true("tomorrow (datetime)", $date->is_future());
  }
  
  function test_is_this_year()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_true("yesterday (date)", $date->is_this_year());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_true("today (date)", $date->is_this_year());
    
    $date = new Time(date('Y-m-d', strtotime('-1 year')), 'datetime');
    $this->assert_false("last year", $date->is_this_year());
    
    $date = new Time(date('Y-12-31 23:59:59'), 'datetime');
    $this->assert_true("", $date->is_this_year());
    
    $date = new Time(date('Y-01-01 00:00:01'), 'datetime');
    $this->assert_true("", $date->is_this_year());
    
    $date = new Time(date('Y-12-31 23:59:59', strtotime('+1 year')), 'datetime');
    $this->assert_false("", $date->is_this_year());
    
    $date = new Time(date('Y-01-01 00:00:01', strtotime('-1 year')), 'datetime');
    $this->assert_false("", $date->is_this_year());
  }
}

new Test_Time();

?>
