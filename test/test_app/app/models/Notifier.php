<?php

class Notifier extends ActionMailer_Base
{
  function monitoring_alert($server)
  {
    $mail = new ActionMailer_Mail('monitoring_alert');
    
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
