<?php

class Notifier extends ActionMailer_Base
{
  function monitoring_alert($server)
  {
    $mail = new ActionMailer_Email('monitoring_alert');
    
    $mail->from('Pointscommuns.com <contact@pointscommuns.com>');
    $mail->recipient($server->email);
    $mail->subject("An error occured on {$server->title}");
    $mail->body(array('server' => $server));
    
    return $mail;
  }
}

?>
