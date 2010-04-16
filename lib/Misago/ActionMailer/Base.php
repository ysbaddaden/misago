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
# = Layouts
# 
# You may render emails inside layouts. By default it searches for mailer's
# layouts, for instance +layouts/user_mailer.html.tpl+ and
# +layouts/user_mailer.plain.tpl+ for a +UserMailer+ class.
# 
# You may also configure a per-class layout, using the +$layout+ property. For
# instance the following class will user +layouts/user_mailer.html.tpl+ and
# +layouts/user_mailer.plain.tpl+:
# 
#   class UserMailer {
#     protected $layout = 'email';
#   }
# 
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
  # Default layout to use.
  protected $layout;
  
  # Template folder containing views.
  public $view_path;
  
  public $helpers = ':all';
  public $params  = array();
  
  # Populated only when delivery_method = 'test'.
  static $deliveries = array();
  
  static function __callStatic($func, $args)
  {
    if (preg_match('/^deliver_(.+)$/', $func, $match))
    {
      $mail = forward_static_call_array(array(get_called_class(), $match[1]), $args);
      return static::deliver($mail);
    }
    trigger_error('No such method '.get_called_class().'::'.$func.'().', E_USER_ERROR);
  }
  
  function __construct()
  {
    if (empty($this->view_path)) {
      $this->view_path = str_replace('\\', '/', String::underscore(get_called_class()));
    }
  }
  
  # Delivers a prepared mail.
  static function deliver($mail)
  {
    if (cfg_get('action_mailer.perform_deliveries', false)) {
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
        static::$deliveries[] = array(
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
      'template' => "{$self->view_path}/{$mail->action}",
      'locals'   => $mail->data,
    );
    
    $options['format'] = 'plain';
    $body_plain = $view->render($options);
    
    $options['format'] = 'html';
    $body_html = $view->render($options);
    
    if (isset($self->layout))
    {
      $body_plain = static::render_layout($self->layout, 'plain', $body_plain, $view, $options);
      $body_html  = static::render_layout($self->layout, 'html',  $body_html,  $view, $options);
    }
    else
    {
      if ($view->template_exists("layouts/{$self->view_path}", 'plain')) {
        $body_plain = static::render_layout($self->view_path, 'plain', $body_plain, $view, $options);
      }
      if ($view->template_exists("layouts/{$self->view_path}", 'html')) {
        $body_html = static::render_layout($self->view_path, 'html', $body_html, $view, $options);
      }
    }
    
    $mail->body_plain = $body_plain;
    $mail->body_html  = $body_html;
    
    return $mail->contents();
  }
  
  private static function render_layout($layout, $format, $content, $view, $options)
  {
    $options['template'] = "layouts/{$layout}";
    $options['format']   = $format;
    $view->yield('content', $content);
    return $view->render($options);
  }
}

?>
