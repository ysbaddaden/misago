<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_ActionMailer_Base extends Unit_TestCase
{
  function test_render()
  {
    $this->fixtures('monitorings');
    $notifier = new Notifier();
    
    $email = $notifier->monitoring_alert(new Monitoring(1));
    $notifier->render($email);
    $this->assert_equal('body_plain', trim($email->body_plain), "An error occured on server1.\n\nPlease check.");
    $this->assert_equal('body_html',  trim($email->body_html),  "<p>An error occured on server1.</p>\n<p>Please check.</p>");
    
    $email = $notifier->monitoring_alert(new Monitoring(2));
    $notifier->render($email);
    $this->assert_equal('body_plain', trim($email->body_plain), "An error occured on server3.\n\nPlease check.");
    $this->assert_equal('body_html',  trim($email->body_html),  "<p>An error occured on server3.</p>\n<p>Please check.</p>");
  }
  
  function test_deliver()
  {
    // ...
  }
}

new Test_ActionMailer_Base();

?>
