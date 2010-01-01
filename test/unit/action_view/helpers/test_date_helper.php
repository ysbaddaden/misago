<?php
require_once __DIR__.'/../../../unit.php';
require_once MISAGO."/lib/Misago/ActionView/Helpers/DateHelper.php";

class Test_ActionView_Helpers_DateHelper extends Misago\Unit\TestCase
{
  function test_distance_of_time_in_words()
  {
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-12 16:45:06'), 'less than a minute');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-12 16:46:00'), 'a minute');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-12 16:40:00'), '5 minutes');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-12 17:40:00'), 'about an hour');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-12 19:40:00'), '3 hours');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-13 16:40:00'), '24 hours');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-13 16:50:00'), 'about a day');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-17 16:40:00'), '5 days');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-12-12 16:45:00'), 'about a month');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2010-05-12 16:40:00'), '6 months');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2010-11-12 16:46:00'), 'about a year');
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2014-11-12 16:40:00'), 'over 5 years');
  }
}

?>
