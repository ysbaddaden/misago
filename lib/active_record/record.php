<?php
/**
 * 
 * @package ActiveRecord
 */
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
  
  /**
   * Sets the record's attributes.
   */
  protected function set_attributes($arg)
  {
    foreach($arg as $attribute => $value) {
      $this->$attribute = $value;
    }
  }
  
  
  # overloading
  
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
  
  
  # iterator
  
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
  
  
  # exportations
  
  function to_xml()
  {
    $xml = '';
    foreach($this->__attributes as $k => $v)
    {
      if (is_string($v)) {
        $v = "<![CDATA[$v]>";
      }
      $xml .= "<$k>$v</$k>";
    }
    $model = String::underscore(get_class($this));
    return "<$model>$xml</$model>";
  }
}

?>
