<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../../test/test_app/config/boot.php';
require_once MISAGO."/lib/action_view/helpers/date_helper.php";

class Test_ActionView_Helpers_DateHelper extends Unit_TestCase
{
  function test_distance_of_time_in_words()
  {
    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-12 16:44:30'), 'one minute ago');
#    $this->assert_equal(distance_of_time_in_words('2009-11-12 16:45:00', '2009-11-12 16:40:00'), '5 minutes');
  }
}

new Test_ActionView_Helpers_DateHelper();

?>
