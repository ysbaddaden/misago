<?php

# Delivers emails.
# 
# =Example
# 
#   class Notifier extends ActionMailer_Base
#   {
#     function signup_notification($recipient)
#     {
#       $mail = new ActionMailer_Mail('signup_notification');
#       $mail->from('me <me@domain>');
#       $mail->to($recipient->email);
#       $mail->body('account' => $recipient);
#       return $mail;
#     }
#   }
# 
# 
# =Configuration
#
# See documentation of ActionMailer_Mail.
# 
# The 'return-path' header may be defined in environment's configuration.
# For instance:
# 
#   cfg::set('mailer_return_path', 'postmaster@domain.com');
# 
# 
# =Mail body
# 
# The body is just a view template. For instance:
# 
# - app/views/notifier/signup_notification.plain.tpl
# - app/views/notifier/signup_notification.html.tpl
# 
# For the time being, it is necessary to have both the
# 'plain' and 'html' templates.
# 
# 
# =Delivering
# 
# Simply call the deliver() method:
# 
#   $notifier = new Notifier();
#   $mail = $notifier->signup_notification($account);
#   $notifier->deliver($mail);
# 
# There is also a magic shortcut:
# 
#   $notifier = new Notifier();
#   $notifier->deliver_signup_notification($account);
# 
class ActionMailer_Base extends Object
{
  public $helpers = ':all';
  public $params  = array();
  
  function __call($func, $args)
  {
    if (preg_match('/^deliver_(.+)$/', $func, $match))
    {
      $mail = call_user_func_array(array($this, $match[1]), $args);
      return $this->deliver($mail);
    }
    trigger_error('No such method '.get_class($this).'::'.$func.'().', E_USER_ERROR);
  }
  
  # Delivers a prepared mail.
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
