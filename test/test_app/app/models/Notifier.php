<?php

class Notifier extends Misago\ActionMailer\Base
{
  function monitoring_alert($server)
  {
    $mail = new Misago\ActionMailer\Mail('monitoring_alert');
    
    $mail->from('Misago <misago@domain.com>');
    $mail->recipient("{$server->title} <{$server->email}>");
    $mail->subject("An error occured on {$server->title}");
    $mail->body(array('server' => $server));
    
    return $mail;
  }
  
  public function render($mail)
  {
    return parent::render($mail);
  }
}

?>
