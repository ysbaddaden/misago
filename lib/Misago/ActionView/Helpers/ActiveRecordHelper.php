<?php

# Helper object to create a HTML form associated to a record.
# 
# Initiate helper for a class:
# 
#   $f = form_for('User');
# 
# Initiate helper for an instance:
# 
#   $user = new User(456);
#   $f = form_for($user);
# 
# Build a form:
# 
#   $f->start(update_user_path($user->id))
#   $f->label('username');
#   $f->text_field('username');
#   submit_tag('Save');
#   $f->end();
# 
# You may also mix records:
# 
#   $p = fields_for($user->profile);
#   $f->start(update_user_path($user->id))
#   
#   $f->label('username');
#   $f->text_field('username');
#   
#   $p->label('about');
#   $p->text_field('about');
#   
#   $f->submit('Save');
#   $f->end();
# 
# IMPROVE: Transparently protect against CSRF attacks (using a hash stored in a cookie/session).
# 
namespace Misago\ActionView\Helpers\ActiveRecordHelper {}

# :namespace: Misago\ActionView\Helpers\ActiveRecordHelper
function error_message_on($object, $column, $all)
{
  if ($object->errors->is_invalid($column))
  {
    $errors = $object->errors->on($column);
    if (!is_array($errors)) {
      $errors = array($errors);
    }
    if ($all)
    {
      $str = "";
      foreach($errors->on($column) as $err) {
        $str .= "$err<br/>";
      }
      return "<span class=\"error\">{$str}</span>";
    }
    else {
      return "<span class=\"error\">{$errors[0]}</span>";
    }
  }
}

# :namespace: Misago\ActionView\Helpers\ActiveRecordHelper
function error_messages_for($object)
{
  $objects = func_get_args();
  $str = '';
  
  foreach($objects as $object)
  {
    $errors = $object->errors->on_base();
    if (!empty($errors))
    {
      if (!is_array($errors)) {
        $errors = array($errors);
      }
      foreach($errors as $err) {
        $str .= "<li>$err</li>";
      }
    }
  }
  
  if (!empty($str)) {
    return "<ul class=\"errors\">{$str}</ul>";
  }
}

?>
