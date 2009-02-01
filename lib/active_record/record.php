<?php

class ActiveRecord_Record extends Object implements Iterator
{
  protected $__attributes = array();
  
  function __construct(array $attributes)
  {
    $this->__attributes = $attributes; 
  }
  
  
  function __get($attr)
  {
    return isset($this->__attributes[$attr]) ?
      $this->__attributes[$attr] : null;
  }
  
  function __set($attr, $value)
  {
    return $this->__attributes[$attr] = $value;
  }
  
  function __isset($attr)
  {
    return isset($this->__attributes[$attr]);
  }
  
  function __unset($attr)
  {
    unset($this->__attributes[$attr]);
  }
  
  
  function rewind()
  {
    return reset($this->__attributes);
  }
  
  function current()
  {
    return current($this->__attributes);
  }
  
  function key()
  {
    return key($this->__attributes);
  }
  
  function next()
  {
    return next($this->__attributes);
  }
  
  function valid()
  {
    return ($this->current() !== false);
  }
}

?>
