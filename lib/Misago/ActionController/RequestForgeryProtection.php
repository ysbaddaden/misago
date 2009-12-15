<?php
namespace Misago\ActionController;

class InvalidAuthenticityToken extends \Misago\Exception {
  protected $default_code = 403;
}

class RequestForgeryProtection extends Caching
{
  # Declares the protection against forgeries.
  static protected function protect_from_forgery($options=array())
  {
    if (protect_against_forgery()) {
      static::prepend_before_filter('verify_authenticity_token', $options);
    }
  }
  
  protected function verify_authenticity_token()
  {
    if (!$this->is_verified_request()) {
      throw new InvalidAuthenticityToken('');
    }
  }
  
  protected function is_verified_request()
  {
    return (!protect_against_forgery()
      and $this->request->method == 'get'
      and isset($_POST['_token'])
      and $_POST['_token'] = $_SESSION['csrf_id']);
  }
}

# Returns true if we want to protect against forgeries, false otherwise.
# 
# Since using tokens in a test environment is troublesome, you may disable
# it completely by setting 'action_controller.protect_against_forgeries':
# 
#   cfg_set('action_controller.allow_forgery_protection', false);
# 
function protect_against_forgery() {
  return cfg_get('action_controller.allow_forgery_protection', true);
}

function form_authenticity_token()
{
  if (!isset($_SESSION))
  {
    throw new InvalidAuthenticityToken("Protection against forgery requests ".
      "(CSRF) requires a valid session.", 500);
  }
  if (!isset($_SESSION['csrf_id'])) {
    $_SESSION['csrf_id'] = md5(uniqid(rand()), true);
  }
  return $_SESSION['csrf_id'];
}

function token_tag() {
  return hidden_field_tag('_token', form_authenticity_token());
}

?>
