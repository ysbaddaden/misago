<?php
namespace Misago\ActionMailer;
use Misago\ActionView;
use Misago\ActiveSupport\String;

# Delivers emails.
# 
# =Example
# 
#   class Notifier extends Misago\ActionMailer\Base
#   {
#     static function signup_notification($recipient)
#     {
#       $mail = new Misago\ActionMailer\Mail('signup_notification');
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
# See documentation of <tt>\Misago\ActionMailer\Mail</tt> for configuration methods.
# 
# =Mail body
# 
# The body is just a view template. For instance:
# 
# - +app/views/notifier/signup_notification.plain.tpl+
# - +app/views/notifier/signup_notification.html.tpl+
# 
# To pass data from the ActionMailer to the view template,
# you may use the <tt>Misago\ActionMailer\Mail::body()</tt> method.
# 
# Please note that for the time being, it is necessary to have
# both the +plain+ and +html+ templates.
# 
# =Delivering
# 
# Simply call the <tt>deliver</tt> method:
# 
#   $mail = Notifier::signup_notification($account);
#   Notifier::deliver($mail);
# 
# There is also a magic shortcut:
# 
#   Notifier::deliver_signup_notification($account);
# 
# =Configuration option
# 
# You may set the following configuration options (using +cfg_set()+).
# 
# - +action_mailer.perform_deliveries+ - set to false to prevent all email from being sent. Set to true otherwise (default).
# - +action_mailer.delivery_method+    - defines a delivery method, either 'sendmail' or 'test'.
# - +action_mailer.return_path+        - you may define a default return-path for all your emails.
# 
class Base extends \Misago\Object
{
  public $helpers = ':all';
  public $params  = array();
  
  # Populated only when delivery_method = 'test'.
  public $deliveries = array();
  
  static function __callStatic($func, $args)
  {
    if (preg_match('/^deliver_(.+)$/', $func, $match))
    {
      $mail = forward_static_call(array(get_called_class(), $match[1]), $args);
      return static::deliver($mail);
    }
    trigger_error('No such method '.get_called_class().'::'.$func.'().', E_USER_ERROR);
  }
  
  # Delivers a prepared mail.
  static function deliver($mail)
  {
    if (cfg_get('action_mailer.perform_deliveries', false))
    {
      return true;
    }
    $contents = static::render($mail);
    $headers  = '';
    foreach($mail->headers() as $k => $v) {
      $headers .= "$k: $v\r\n";
    }
    
    switch(cfg_get('action_mailer.delivery_method'))
    {
      case 'test':
        $this->deliveries[] = array(
          'mailer'     => get_called_class(),
          'action'     => $mail->action,
          'recipients' => $mail->recipients(),
          'subject'    => $mail->subject,
          'contents'   => $contents,
          'headers'    => $headers,
        );
        return true;
      break;
      
      case 'sendmail':
        return mb_send_mail($mail->recipients(), $mail->subject, $contents, $headers);
      break;
      
      default:
        throw new \Misago\Exception("Mailer error: unknown delivery method '".
          cfg_get('action_mailer.delivery_method')."'.", 500);
    }
  }
  
  static protected function render($mail)
  {
    $self = new static();
    $view = new ActionView\Base($self);
    
    $options = array(
      'template' => String::underscore(get_called_class()).'/'.$mail->action,
      'locals'   => $mail->data,
      'layout'   => false,
    );
    
    $options['format'] = 'plain';
    $mail->body_plain = $view->render($options);
    
    $options['format'] = 'html';
    $mail->body_html = $view->render($options);
    
    return $mail->contents();
  }
}

?>
