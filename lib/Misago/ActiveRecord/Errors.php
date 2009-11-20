<?php
namespace Misago\ActiveRecord;
use Misago\ActiveSupport\String;
use Misago\I18n;

# Handles errors for a given record.
# 
# FIXME: Base error messages should be translated.
# 
class Errors
{
  private $model;
  private $model_name;
  private $base_messages = array();
  private $messages      = array();
  
  function __construct($model=null)
  {
    if ($model !== null)
    {
      $this->model = $model;
      $this->model_name = String::underscore(get_class($model));
    }
  }
  
  function __get($attribute)
  {
    if (isset($this->$attribute)) {
      return $this->$attribute;
    }
    trigger_error("No such attribute Misago\ActiveRecord\Errors::$attribute.", E_USER_NOTICE);
  }
  
  function __toString()
  {
    $str  = print_r($this->base_messages, true);
    $str .= print_r($this->messages, true);
    return $str;
  }
  
  # Adds an error message for the associated record's attribute.
  # The attribute is now marked as invalid.
  function add($attribute, $msg=':invalid')
  {
    if (empty($this->messages[$attribute])) {
      $this->messages[$attribute] = array();
    }
    $this->messages[$attribute][] = $msg;
  }
  
  # Shortcut for +<tt>add</tt>($attribute, ':blank')+.
  function add_on_blank($attribute)
  {
    $this->add($attribute, ':blank');
  }
  
  # Shortcut for +<tt>add</tt>($attribute, ':empty')+.
  function add_on_empty($attribute)
  {
    $this->add($attribute, ':empty');
  }
  
  # Adds an error message not related to a particular attribute.
  function add_to_base($msg)
  {
    $this->base_messages[] = $msg;
  }
  
  # Clears all error messages.
  # All attributes are now considered valid.
  function clear()
  {
    $this->base_messages = array();
    $this->messages      = array();
  }
  
  # Counts how many error messages there are.
  function count()
  {
    $count = count($this->base_messages);
    foreach($this->messages as $messages) {
      $count += count($messages);
    }
    return $count;
  }
  
  # Returns the full list of error messages.
  function & full_messages()
  {
    $ary = $this->base_messages;
    
    foreach(array_keys($this->messages) as $attribute)
    {
      $messages = $this->on($attribute);
      
      if (is_array($messages)) {
        $ary = array_merge($ary, $messages);
      }
      else {
        $ary[] = $messages;
      }
    }
    
    return $ary;
  }
  
  # Returns true if all attributes are valid and no error message was added.
  function is_empty()
  {
    return (empty($this->messages) and empty($this->base_messages));
  }
  
  # Returns true if associated record attribute is invalid.
  function is_invalid($attribute)
  {
    return (isset($this->messages[$attribute]));
  }
  
  # Returns error messages for associated record attribute.
  # Returns null if there is no error.
  # 
  # TODO: Add count interpolation variable for validate_length_of().
  function on($attribute)
  {
    if (!empty($this->messages[$attribute]))
    {
      foreach($this->messages[$attribute] as $i => $msg)
      { 
        if (is_symbol($msg)) {
          $msg = substr($msg, 1);
        }
        
        if (isset($this->model))
        {
          $options = array(
            'model'     => $this->model->human_name(),
            'attribute' => $this->model->human_attribute_name($attribute),
            'value'     => $this->model->$attribute,
            'context'   => "active_record.errors.models.{$this->model_name}.attributes.$attribute",
          );
          $translation = I18n::do_translate($msg, $options);
          if ($translation === null)
          {
            $options['context'] = "active_record.errors.models.{$this->model_name}";
            $translation = I18n::do_translate($msg, $options);
            if ($translation === null)
            {
              $options['context'] = 'active_record.errors.messages';
              $translation = I18n::translate($msg, $options);
            }
          }
        }
        else
        {
          $options = array(
            'attribute' => String::humanize($attribute),
            'context'   => 'active_record.errors.messages',
          );
          $translation = I18n::translate($msg, $options);
        }
        $this->messages[$attribute][$i] = $translation;
      }
      return (count($this->messages[$attribute]) > 1) ?
        $this->messages[$attribute] : $this->messages[$attribute][0];
    }
    return null;
  }
  
  # Returns error messages for associated record (not related to a particular attribute).
  function on_base()
  {
    if (!empty($this->base_messages))
    {
      return (count($this->base_messages) > 1) ?
        $this->base_messages : $this->base_messages[0];
    }
    return null;
  }
  
  function to_xml()
  {
    $str  = '<?xml version="1.0" encoding="UTF-8"?>';
    $str .= '<errors>';
    foreach($this->full_messages() as $message) {
      $str .= "<error>$message</error>";
    }
    $str .= '</errors>';
    return $str;
  }
  
  function to_json()
  {
    return json_encode($this->full_messages());
  }
}

?>
