<?php

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
