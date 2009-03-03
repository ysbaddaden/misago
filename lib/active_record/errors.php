<?php
/**
 * 
 * 
 * @package ActiveRecord
 * @subpackage Validations
 * 
 * TODO: Write tests for ActiveRecord::Errors.
 * TODO: Transform symbols to full text error messages.
 */
class ActiveRecord_Errors /*implements Iterator*/
{
  private $base_messages = array();
  private $messages      = array();
  
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
  
  # FIXME: ActiveRecord_Errors::full_messages() -> how to concatenate arrays in PHP?
  function full_messages()
  {
    $messages = $this->base_messages;
    foreach($this->messages as $_messages)
    {
      $messages .= $_messages;
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
  
  /*
  function to_xml()
  {
    $xml = '<?xml version="1.0" encoding="UTF-8"?><errors>';
    foreach($this->base_messages as $msg) {
      $xml .= "<error>$msg</error>";
    }
    foreach($this->messages as $name => $messages)
    {
      foreach($messages as $msg) {
        $xml .= "<error on=\"$name\">$msg</error>";
      }
    }
    $xml .= "</errors>";
    return $xml;
  }
  */
  
  # Iterator
  
  /*
  function rewind() {
    return reset($this->messages);
  }
  
  function current() {
    return current($this->messages);
  }
  
  function key() {
    return key($this->messages);
  }
  
  function next() {
    return next($this->messages);
  }
  
  function valid() {
    return ($this->current() !== false);
  }
  */
  
  /*
  private function flatten_messages()
  {
    $flatten_messages = array();
    foreach($this->messages as $name => $messages)
    {
      foreach($messages as $message) {
        $flatten_messages[] = array($name, $message)
      }
    }
    return $flatten_messages;
  }
  */
}

?>
