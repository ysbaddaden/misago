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
  
  function add($attribute, $msg=':invalid')
  {
    if (empty($this->messages[$attribute])) {
      $this->messages[$attribute] = array();
    }
    $this->messages[$attribute][] = $msg;
  }
  
  function add_on_blank($attribute)
  {
    $this->add($attribute, ':blank');
  }
  
  function add_on_empty($attribute)
  {
    $this->add($attribute, ':empty');
  }
  
  function add_to_base($msg)
  {
    $this->base_messages[] = $msg;
  }
  
  function clear()
  {
    $this->base_messages = array();
    $this->messages      = array();
  }
  
  function count()
  {
    $count = count($this->base_messages);
    foreach($this->messages as $messages) {
      $count += count($messages);
    }
    return $count;
  }
  
  function full_messages()
  {
    $messages = $this->base_messages;
    foreach($this->messages as $_messages) {
      $messages = array_merge($messages, $_messages);
    }
    return $messages;
  }
  
  function is_empty()
  {
    return empty($this->messages);
  }
  
  function is_invalid($attribute)
  {
    return (isset($this->messages[$attribute]));
  }
  
  function on($attribute)
  {
    if (!empty($this->messages[$attribute]))
    {
      foreach($this->messages[$attribute] as $i => $msg)
      { 
        if (is_symbol($msg)) {
          $msg = t(substr($msg, 1), 'active_record.errors.messages');
        }
        $this->messages[$attribute][$i] = str_replace("{{attribute}}", String::humanize($attribute), $msg);
      }
      return (count($this->messages[$attribute]) > 1) ?
        $this->messages[$attribute] : $this->messages[$attribute][0];
    }
    return null;
  }
  
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
