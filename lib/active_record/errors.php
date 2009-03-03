<?php
/**
 * 
 * 
 * @package ActiveRecord
 * @subpackage Validations
 * 
 * IMPROVE: Extract ActiveRecord::Errors::$symbol_messages into a configurable/translatable YAML file.
 */
class ActiveRecord_Errors
{
  private $base_messages   = array();
  private $messages        = array();
  private $symbol_messages = array(
    ':invalid' => "{{attribute}} is invalid",
    ':blank'   => "{{attribute}} can't be blank",
    ':empty'   => "{{attribute}} can't be empty",
  );
  
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
  
  # FIXME: ActiveRecord_Errors::full_messages() -> how do we concatenate arrays in PHP?
  function full_messages()
  {
    $messages = $this->base_messages;
    foreach($this->messages as $_messages) {
      $messages += $_messages;
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
          $msg = $this->symbol_messages[$msg];
        }
        $this->messages[$attribute][$i] = str_replace("{attribute}", String::humanize($attribute), $msg);
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
