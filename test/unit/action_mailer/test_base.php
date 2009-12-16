<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../test/test_app/config/boot.php';

class Test_ActionMailer_Base extends Misago\Unit\TestCase
{
  function test_render()
  {
    $this->fixtures('monitorings');
    
    $mail = Notifier::monitoring_alert(new Monitoring(1));
    Notifier::render($mail);
    
    $this->assert_equal(trim($mail->body_plain), "An error occured on server1.\n\nPlease check.");
    $this->assert_equal(trim($mail->body_html),  "<p>An error occured on server1.</p>\n<p>Please check.</p>");
    
    $mail = Notifier::monitoring_alert(new Monitoring(2));
    Notifier::render($mail);
    $this->assert_equal(trim($mail->body_plain), "An error occured on server3.\n\nPlease check.");
    $this->assert_equal(trim($mail->body_html),  "<p>An error occured on server3.</p>\n<p>Please check.</p>");
  }
  
#  function test_deliver()
#  {
#    $mail = Notifier::monitoring_alert(new Monitoring(3));
#    Notifier::deliver($mail);
#    
#    Notifier::deliver_monitoring_alert(new Monitoring(3));
#  }
}

new Test_ActionMailer_Base();
?>
