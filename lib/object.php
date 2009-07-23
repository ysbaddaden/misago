<?php

# Generic object, to share methods between all misago's classes.
abstract class Object
{
  protected $attr_read = array();
  protected $__mapped_attributes = array();
  protected $__mapped_methods    = array();
  
  function __get($attr)
  {
    if (in_array($attr, $this->attr_read)) {
      return $this->$attr;
    }
    elseif (isset($this->__mapped_attributes[$attr])) {
      return $this->__mapped_attributes[$attr]['object']->{$this->__mapped_attributes[$attr]['attribute']};
    }
    return null;
  }
  
  function __set($attr, $value)
  {
    if (isset($this->__mapped_attributes[$attr]))
    {
      $this->__mapped_attributes[$attr]['object']->{$this->__mapped_attributes[$attr]['attribute']} = $value;
      return $value;
    }
    return $this->$attr = $value;
  }
  
  function __call($func, $args)
  {
    if (isset($this->__mapped_methods[$func])) {
      call_user_func_array($this->__mapped_methods[$func], &$args);
    }
    trigger_error("No such method ".get_class($this)."::$func().", E_USER_ERROR);
  }
  
  function map_module($behavior, $attributes=array(), $functions=array())
  {
    foreach($attributes as $mapped => $attr) {
      $this->__mapped_attributes[$mapped] = array('object' => $behavior, 'attribute' => $attr);
    }
    foreach($functions as $mapped => $func) {
      $this->__mapped_methods[$mapped] = array($behavior, $func);
    }
  }
  
  function to_s()
  {
    return $this->__toString();
  }
  
  function to_xml()
  {
    return $this->to_s();
  }

  function to_json()
  {
    return $this->to_s();
  }
}

?>
