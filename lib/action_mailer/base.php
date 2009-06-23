<?php

class ActionMailer_Base extends Object
{
  public $helpers = ':all';
  public $params  = array();
  
  function deliver($mail)
  {
    $this->render($mail);
    mail($mail->to, $mail->subject, $mail->get_contents(), $mail->get_headers());
  }
  
  protected function render($mail)
  {
    $view = new ActionView_Base($this);
    
    $mail->body_plain = $view->render(array(
      'format' => 'plain',
      'action' => $mail->action,
      'locals' => $mail->data
    ));
    $mail->body_html  = $view->render(array(
      'format' => 'html',
      'action' => $mail->action,
      'locals' => $mail->data
    ));
  }
}

?>
