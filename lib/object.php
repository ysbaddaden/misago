<?php

# Generic object, to share methods between all misago's classes.
abstract class Object
{
  # A collection of attributes that must be accessible read-only (they must be protected).
  protected $attr_read = array();
  
  private   $__mapped_attributes = array();
  private   $__mapped_methods    = array();
  
  function __get($attr)
  {
    if (in_array($attr, $this->attr_read)) {
      return $this->$attr;
    }
    elseif (isset($this->__mapped_attributes[$attr])) {
      return $this->__mapped_attributes[$attr]['object']->{$this->__mapped_attributes[$attr]['attribute']};
    }
    elseif (method_exists($this, $attr)) {
      return $this->$attr();
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
    elseif (method_exists($this, $attr)) {
      return $this->$attr($value);
    }
    return $this->$attr = $value;
  }
  
  function __call($func, $args)
  {
    if (isset($this->__mapped_methods[$func])) {
      return call_user_func_array($this->__mapped_methods[$func], $args);
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
  
  function to_yaml()
  {
    return $this->to_s();
  }
  
  function to_array()
  {
    return $this->to_s();
  }
}

?>
