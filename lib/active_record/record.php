<?php

# Generic Record.
# 
# * A record consists on a list of attributes.
# * You may iterate throught attributes (like an array).
# 
# @package ActiveRecord
# IMPROVE: Add serialization support throught the __wakeup() and __sleep() magic methods.
abstract class ActiveRecord_Record extends Object implements Iterator
{
  protected $__attributes = array();
  protected $new_record   = true;
  
  function __construct(array $attributes=null)
  {
    if (!empty($this->attributes)) {
      $this->__attributes = $attributes;
    }
  }
  
  # Sets the record's attributes.
  protected function set_attributes($arg)
  {
    foreach($arg as $attribute => $value) {
      $this->$attribute = $value;
    }
  }
  
  
  function __get($attr)
  {
    return isset($this->__attributes[$attr]) ?
      $this->__attributes[$attr] : null;
  }
  
  function __set($attr, $value) {
    return $this->__attributes[$attr] = $value;
  }
  
  function __isset($attr) {
    return isset($this->__attributes[$attr]);
  }
  
  function __unset($attr) {
    unset($this->__attributes[$attr]);
  }
  
  
  function rewind() {
    return reset($this->__attributes);
  }
  
  function current() {
    return current($this->__attributes);
  }
  
  function key() {
    return key($this->__attributes);
  }
  
  function next() {
    return next($this->__attributes);
  }
  
  function valid() {
    return ($this->current() !== false);
  }
  
  
  function __sleep()
  {
  	return array_keys($this->__attributes);
  }
  
  function __wakeup()
  {
  	
  }
  
  
  # Exports the record as XML.
  function to_xml()
  {
    $attributes = $this->__sleep();
    $xml = '';
    foreach($attributes as $k)
    {
    	$v = is_string($this->$k) ? "<![CDATA[".$this->$k."]>" : $this->$k;
      $xml .= "<$k>$v</$k>";
    }
    $model = String::underscore(get_class($this));
    return "<$model>$xml</$model>";
  }
  
  # Exports the record as JSON.
  function to_json()
  {
    $attributes = $this->__sleep();
  	$data = array();
    foreach($attributes as $k) {
    	$data[$k] = $this->$k;
    }
  	return json_encode($data);
  }
}

?>
