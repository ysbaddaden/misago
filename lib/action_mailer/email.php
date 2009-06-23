<?php

class ActionMailer_Email extends Object
{
  public $action;
  
  public $subject    = '';
  public $from       = '';
  public $reply_to   = null;
  public $recipients = array();
  public $cc         = array();
  public $bcc        = array();
  public $data       = array();
  
  function __construct($action)
  {
    $this->action = $action;
  }
  
  function subject($subject)
  {
    $this->subject = $subject;
  }
  
  function from($from)
  {
    $this->from = $from;
  }
  
  function reply_to($reply_to)
  {
    $this->reply_to = $reply_to;
  }
  
  function recipient($recipient)
  {
    $recipients = func_get_args();
    $this->add_recipients($recipients, 'to');
  }
  
  function cc($recipient)
  {
    $recipients = func_get_args();
    $this->add_recipients($recipients, 'cc');
  }
  
  function bcc($recipient)
  {
    $recipients = func_get_args();
    $this->add_recipients($recipients, 'bcc');
  }
  
  protected function add_recipients($recipients, $type='to')
  {
    foreach($recipients as $recipient) {
      $this->{$type}[] = $recipient;
    }
  }
  
  function body($data_for_body)
  {
    $this->data =& $data_for_body;
  }
  
  function get_headers()
  {
    $mail->headers[] = 'From: '.$mail->from;
    $mail->headers[] = 'To: '.implode(', ', $mail->to);
    
    if (!empty($mail->reply_to)) {
      $mail->headers[] = 'Reply-To: '.$mail->reply_to;
    }
    if (!empty($mail->cc)) {
      $mail->headers[] = 'Cc: '.implode(', ', $mail->cc);
    }
    if (!empty($mail->bcc)) {
      $mail->headers[] = 'Bcc: '.implode(', ', $mail->bcc);
    }
  }
  
  function get_contents()
  {
    return $this->body_plain;
  }
}

?>
