<?php
require_once __DIR__.'/../../unit.php';

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
  
  function test_render_with_default_layout()
  {
    $mail = UserMailer::welcome();
    UserMailer::render($mail);
    
    $this->assert_equal(trim($mail->body_plain), "My wonderful website\n\nWelcome!");
    $this->assert_equal(trim($mail->body_html),  "<h1>My wonderful website</h1>\n\n<p>Welcome!</p>");
  }
  
  function test_render_with_declared_layout()
  {
    $mail = AccountMailer::welcome();
    AccountMailer::render($mail);
    
    $this->assert_equal(trim($mail->body_plain), "My Website\n\nWelcome!");
    $this->assert_equal(trim($mail->body_html),  "<h1>My Website</h1>\n\n<p>Welcome!</p>");
  }
  
#  function test_deliver()
#  {
#    $mail = Notifier::monitoring_alert(new Monitoring(3));
#    Notifier::deliver($mail);
#    
#    Notifier::deliver_monitoring_alert(new Monitoring(3));
#  }
}

?>
