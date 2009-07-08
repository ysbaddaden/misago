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
# =Mail
#
# See documentation of ActionMailer_Mail for configuration methods.
# 
# =Mail body
# 
# The body is just a view template. For instance:
# 
# - app/views/notifier/signup_notification.plain.tpl
# - app/views/notifier/signup_notification.html.tpl
# 
# To pass data from the ActionMailer to the view template,
# you may use the `body()` method.
# 
# Please note that for the time being, it is necessary to have
# both the 'plain' and 'html' templates.
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
# =Configuration option
# 
# You may set the following configuration options (using cfg::set()).
# 
# - mailer_perform_deliveries: set to false to prevent all email from being sent. Set to true otherwise (default).
# - mailer_delivery_method: defines a delivery method. Only 'sendmail' is supported right now.
# - mailer_return_path: you may define a default return-path for all your emails.
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
    if (cfg::is_set('mailer_perform_deliveries') and !cfg::get('mailer_perform_deliveries')) {
      return true;
    }
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
