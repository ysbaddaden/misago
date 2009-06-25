<?php

class ActionMailer_Mail extends Object
{
  public $action;
  
  public $subject  = '';
  public $from     = '';
  public $reply_to = null;
  public $to       = '';
  public $cc       = array();
  public $bcc      = array();
  public $data     = array();
  
  public $headers  = array(
    'MIME-Version' => '1.0',
  );
  
  
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
  
  # Add some recipients.
  function recipient($recipient)
  {
    $recipients = func_get_args();
    $this->add_recipients($recipients, 'to');
  }
  
  # Add some recipients in copy.
  function cc($recipient)
  {
    $recipients = func_get_args();
    $this->add_recipients($recipients, 'cc');
  }
  
  # Add some hidden recipients in copy.
  function bcc($recipient)
  {
    $recipients = func_get_args();
    $this->add_recipients($recipients, 'bcc');
  }
  
  # Data to be passed to the view.
  function body($data_for_body)
  {
    $this->data =& $data_for_body;
  }
  
  # Returns the list of recipients (to).
  function recipients()
  {
    return implode(', ', $this->to);
  }
  
  # Returns the headers.
  function headers()
  {
    $headers = $this->headers;
    
    $headers['From'] = $this->from;
    if (!empty($this->reply_to)) {
      $headers['Reply-To'] = $this->reply_to;
    }
    if (!empty($this->cc)) {
      $headers['Cc'] = implode(', ', $this->cc);
    }
    if (!empty($this->bcc)) {
      $headers['Bcc'] = implode(', ', $this->bcc);
    }
    
    if (!empty($this->return_path)) {
      $header['Return-Path'] = $this->return_path;
    }
    elseif (cfg::is_set('mailer_return_path')) {
      $header['Return-Path'] = cfg::get('mailer_return_path');
    }
    
    return $headers;
  }
  
  # Returns mail's multipart body.
  function contents()
  {
    $mime_boundary = "=boundary=".md5(time()).'=';
    
    # headers
    $this->headers['Content-Type'] = 'multipart/alternative; boundary="'.$mime_boundary.'"';
    $this->headers['Content-Transfer-Encoding'] = '8bit';
    
    # text/html
    $contents  = "--{$mime_boundary}\n";
    $contents .= "Content-Type: text/html; charset=utf-8\n";
    $contents .= "Content-Transfer-Encoding: 8bit\n\n";
    $contents .= $this->body_html."\n";
    
    # text/plain
    $contents .= "--{$mime_boundary}\n";
    $contents .= "Content-Type: text/plain; charset=utf-8\n";
    $contents .= "Content-Transfer-Encoding: 8bit\n\n";
    $contents .= wordwrap($this->body_plain, 70)."\n";
    
    # final boundary
    $contents .= "--{$mime_boundary}--\n\n";
    
    return $contents;
  }
  
  protected function add_recipients($recipients, $type='to')
  {
    foreach($recipients as $recipient) {
      $this->{$type}[] = $recipient;
    }
  }
}

?>
