<?php
/**
 * 
 * 
 * @package ActiveRecord
 * @subpackage Validations
 */
class ActiveRecord_Errors
{
  private $base_messages   = array();
  private $messages        = array();
  
  function __get($attribute)
  {
    if (isset($this->$attribute)) {
      return $this->$attribute;
    }
    trigger_error("No such attribute ActiveRecord_Errors::$attribute.", E_USER_NOTICE);
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
  
  # Shortcut for add($attribute, ':blank').
  function add_on_blank($attribute)
  {
    $this->add($attribute, ':blank');
  }
  
  # Shortcut for add($attribute, ':empty').
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
  function full_messages()
  {
    $messages = $this->base_messages;
    foreach($this->messages as $_messages) {
      $messages = array_merge($messages, $_messages);
    }
    return $messages;
  }
  
  # Returns true if all attributes are valid and no error message was added.
  function is_empty()
  {
    return (empty($this->messages) && empty($this->base_messages));
  }
  
  # Returns true if associated record attribute is invalid.
  function is_invalid($attribute)
  {
    return (isset($this->messages[$attribute]));
  }
  
  # Returns error messages for associated record attribute.
  # Returns null if there is no error.
  # 
  # TODO: Try to translate in ActiveRecord's contexts ('active_record.errors.models.<model>.attributes.<attribute>' & 'active_record.errors.models.<model>'), before translating in the generic one ('active_record.errors.messages').
  function on($attribute)
  {
    if (!empty($this->messages[$attribute]))
    {
      foreach($this->messages[$attribute] as $i => $msg)
      { 
        if (is_symbol($msg)) {
          $msg = substr($msg, 1);
        }
        $this->messages[$attribute][$i] = I18n::translate($msg, array(
          'context' => 'active_record.errors.messages',
#          'context' => array(
#            "active_record.errors.models.$model.attributes.$attribute",
#            "active_record.errors.models.$model",
#            'active_record.errors.messages',
#          ),
          'attribute' => String::humanize($attribute)
        ));
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
}

?>
