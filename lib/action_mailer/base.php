<?php

class ActionMailer_Base extends Object
{
  public $helpers = ':all';
  public $params  = array();
  
  function __call($func, $args)
  {
    if (preg_match('/^deliver_(.+)$/', $func, $match) === 0)
    {
      $mail = call_user_func_array(array($this, $match[1]), $args);
      return $this->deliver($mail);
    }
    trigger_error('No such method '.get_class($this).'::'.$func.'().', E_USER_ERROR);
  }
  
  function deliver($mail)
  {
    $contents = $this->render($mail);
    $headers  = '';
    foreach($mail->headers() as $k => $v) {
      $headers .= "$k: $v\r\n";
    }
    return mb_send_mail($mail->recipients(), $mail->subject, $contents, $headers);
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
    
    return $mail->contents();
  }
}

?>
