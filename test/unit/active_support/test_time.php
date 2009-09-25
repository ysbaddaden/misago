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
    $this->assert_equal((string)$obj, '21:52:23');
    
    $obj = new Time('2009-01-21', 'date');
    $this->assert_equal((string)$obj, '2009-01-21');
    
    $obj = new Time('2009-01-21 09:52:23', 'datetime');
    $this->assert_equal((string)$obj, '2009-01-21 09:52:23');
    
    $obj = new Time('', 'datetime');
    $this->assert_equal((string)$obj, '');
    
    # how to test null = now?
#    $obj = new Time(null, 'datetime');
#    $this->assert_equal((string)$obj, '????', 'null time is now');
  }
  
  function test_magic_attributes()
  {
    $obj = new Time('2009-10-05');
    $this->assert_equal($obj->year, '2009');
    $this->assert_equal($obj->month, '10');
    $this->assert_equal($obj->day, '05');
    
    $obj = new Time('1985-06-30');
    $this->assert_equal($obj->year, '1985');
    $this->assert_equal($obj->month, '06');
    $this->assert_equal($obj->day, '30');
    
    $obj = new Time('1976-01-31 00:45:32');
    $this->assert_equal($obj->year, '1976');
    $this->assert_equal($obj->month, '01');
    $this->assert_equal($obj->day, '31');
    $this->assert_equal($obj->hour, '00');
    $this->assert_equal($obj->min, '45');
    $this->assert_equal($obj->sec, '32');
  }
  
  function test_is_today()
  {
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_true($date->is_today());
    
    $date = new Time(date('Y-m-d H:i:s'), 'datetime');
    $this->assert_true($date->is_today());
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_false($date->is_today(), "the day before yesterday isn't today");
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_false($date->is_today(), "tomorrow is not today");
  }
  
  function test_is_yesterday()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_true($date->is_yesterday());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('-1 day')), 'datetime');
    $this->assert_true($date->is_yesterday());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_false($date->is_yesterday(), "today is not yesterday");
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_false($date->is_yesterday(), "the day before yesterday isn't yesterday either");
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_false($date->is_yesterday(), "tomorrow isn't yesterday");
  }
  
  function test_is_past()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_true($date->is_past());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('-1 day')), 'datetime');
    $this->assert_true($date->is_past());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_false($date->is_past(), 'today is present');
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_true($date->is_past());
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_false($date->is_past());
  }
  
  function test_is_tomorrow()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_false($date->is_tomorrow());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('-1 day')), 'datetime');
    $this->assert_false($date->is_tomorrow());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_false($date->is_tomorrow());
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_false($date->is_tomorrow());
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_true($date->is_tomorrow());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('+1 day')), 'datetime');
    $this->assert_true($date->is_tomorrow());
  }
  
  function test_is_future()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_false($date->is_future());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('-1 day')), 'datetime');
    $this->assert_false($date->is_future());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_false($date->is_future());
    
    $date = new Time(date('Y-m-d', strtotime('-2 days')), 'date');
    $this->assert_false($date->is_future());
    
    $date = new Time(date('Y-m-d', strtotime('+1 day')), 'date');
    $this->assert_true($date->is_future());
    
    $date = new Time(date('Y-m-d H:i:s', strtotime('+1 day')), 'datetime');
    $this->assert_true($date->is_future());
  }
  
  function test_is_this_year()
  {
    $date = new Time(date('Y-m-d', strtotime('-1 day')), 'date');
    $this->assert_true($date->is_this_year());
    
    $date = new Time(date('Y-m-d'), 'date');
    $this->assert_true($date->is_this_year());
    
    $date = new Time(date('Y-m-d', strtotime('-1 year')), 'datetime');
    $this->assert_false($date->is_this_year());
    
    $date = new Time(date('Y-12-31 23:59:59'), 'datetime');
    $this->assert_true($date->is_this_year());
    
    $date = new Time(date('Y-01-01 00:00:01'), 'datetime');
    $this->assert_true($date->is_this_year());
    
    $date = new Time(date('Y-12-31 23:59:59', strtotime('+1 year')), 'datetime');
    $this->assert_false($date->is_this_year());
    
    $date = new Time(date('Y-01-01 00:00:01', strtotime('-1 year')), 'datetime');
    $this->assert_false($date->is_this_year());
  }
}

new Test_Time();

?>
