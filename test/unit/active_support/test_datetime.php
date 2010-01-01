<?php
require_once __DIR__.'/../../unit.php';
use Misago\ActiveSupport;

class Test_ActiveSupport_Datetime extends Test\Unit\TestCase
{
  function test_constructor()
  {
    $date = new ActiveSupport\Datetime('2009-10-29', 'Europe/Paris');
    $this->assert_equal($date->format('Y-m-d'), '2009-10-29');
    $this->assert_equal((string)$date, '2009-10-29 00:00:00');
    
    $date = new ActiveSupport\Datetime('1895-01-01');
    $this->assert_equal((string)$date, '1895-01-01 00:00:00');
    
    $date = new ActiveSupport\Datetime('999-12-25 00:10:15', 'Europe/London');
    $this->assert_equal((string)$date, '0999-12-25 00:10:15');
  }
  
  function test_invalid_date()
  {
    $date = new ActiveSupport\Datetime('2012-15-64');
    $this->assert_false($date->is_valid());
    $this->assert_equal((string)$date, '2012-15-64');
    
    $date = new ActiveSupport\Datetime('');
    $this->assert_false($date->is_valid());
    $this->assert_equal((string)$date, '');
  }
  
  function test_modify()
  {
    $date = new ActiveSupport\Datetime('2009-08-15 15:05:00');
    $date->modify('first day of next month');
    $this->assert_equal((string)$date, '2009-09-01 15:05:00');
    $date->modify('last day of next month');
    $this->assert_equal((string)$date, '2009-10-31 15:05:00');
  }
  
  function test_to_s()
  {
    $date = new ActiveSupport\Datetime('1968-05-01', 'Europe/Paris');
    $this->assert_equal($date->to_s(), '1968-05-01 00:00:00');
    $this->assert_equal($date->to_s('number'), '19680501000000');
  }
  
  function test_to_iso8601()
  {
    $date = new ActiveSupport\Datetime('1968-05-01 15:12:00', 'Europe/London');
    $this->assert_equal($date->to_iso8601(), '1968-05-01T15:12:00+0100');
    
    $date = new ActiveSupport\Datetime('1968-05-01 15:12:00', 'Asia/Tokyo');
    $this->assert_equal($date->to_iso8601(), '1968-05-01T15:12:00+0900');
  }
  
  function test_to_rfc2822()
  {
    $date = new ActiveSupport\Datetime('1968-05-01 15:12:00', 'Europe/London');
    $this->assert_equal($date->to_rfc2822(), 'Wed, 01 May 1968 15:12:00 +0100');
    
    $date = new ActiveSupport\Datetime('1968-05-01 15:12:00', 'Asia/Tokyo');
    $this->assert_equal($date->to_rfc2822(), 'Wed, 01 May 1968 15:12:00 +0900');
  }
  
  function test_distance()
  {
    $date = new ActiveSupport\Datetime('1995-01-01 15:05:00');
    $this->assert_equal($date->distance('1995-01-01 15:05:05'), 5);
    $this->assert_equal($date->distance('1995-01-01 19:05:05'), 14405);
    $this->assert_equal($date->distance('1994-05-01 19:05:05'), -21153595);
  }
  
  function test_properties()
  {
    $date = new ActiveSupport\Datetime('1995-01-06 15:05:00');
    $this->assert_equal($date->year,  '1995');
    $this->assert_equal($date->month, '01');
    $this->assert_equal($date->day,   '06');
    $this->assert_equal($date->hour,  '15');
    $this->assert_equal($date->min,   '05');
    $this->assert_equal($date->sec,   '00');
  }
  
  function test_date()
  {
    $date = new ActiveSupport\Date('1995-01-06 15:05:00');
    $this->assert_equal((string)$date, '1995-01-06');
  }
  
  function test_time()
  {
    $time = new ActiveSupport\Time('1995-01-06 15:05:00');
    $this->assert_equal((string)$time, '15:05:00');
  }
}

?>
