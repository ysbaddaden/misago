<?php

$location = dirname(__FILE__).'/../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";

class Test_ActionMailer_Base extends Unit_TestCase
{
  function test_render()
  {
    $this->fixtures('monitorings');
    $notifier = new Notifier();
    
    $mail = $notifier->monitoring_alert(new Monitoring(1));
    $notifier->render($mail);
    
    $this->assert_equal(trim($mail->body_plain), "An error occured on server1.\n\nPlease check.");
    $this->assert_equal(trim($mail->body_html),  "<p>An error occured on server1.</p>\n<p>Please check.</p>");
    
    $mail = $notifier->monitoring_alert(new Monitoring(2));
    $notifier->render($mail);
    $this->assert_equal(trim($mail->body_plain), "An error occured on server3.\n\nPlease check.");
    $this->assert_equal(trim($mail->body_html),  "<p>An error occured on server3.</p>\n<p>Please check.</p>");
  }
  
  /*
  function test_deliver()
  {
    $notifier = new Notifier();
    
    $mail = $notifier->monitoring_alert(new Monitoring(3));
    $notifier->deliver($mail);
    
    $notifier->deliver_monitoring_alert(new Monitoring(3));
  }
  */
}

new Test_ActionMailer_Base();

?>
