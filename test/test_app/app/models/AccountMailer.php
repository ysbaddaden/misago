<?php

class AccountMailer extends Misago\ActionMailer\Base
{
  protected $layout = 'email';
  
  static function welcome()
  {
    $mail = new Misago\ActionMailer\Mail('welcome');
    $mail->from('Misago <misago@domain.com>');
    $mail->recipient("somebody <somebody@example.com>");
    $mail->subject("Welcome!");
    return $mail;
  }
  
  static function render($mail) {
    return parent::render($mail);
  }
}

?>
