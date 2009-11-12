<?php

# Generic object, to share methods between all misago's classes.
# 
# Example class:
# 
#   class MyClass extends Misago_Object
#   {
#     protected $id         = 1;
#     private   $new_record = false;
#     
#     function new_record() {
#       return $this->new_record;
#     }
#     
#     function id() {
#       return $this->id;
#     }
#     
#     function id_set($id) {
#       return $this->id = $id;
#     }
#   }
# 
# =Getters and setters methods as attributes
# 
# You may define a list of non public properties that may be
# accessible on read, by using a public method named just like
# the property:
# 
#   $o = new MyClass();
#   $o->new_record         # => returns false
#   $o->new_record = true  # => error: protected attribute
# 
# You may also have setters, using this time a +property_set()+
# method:
# 
#   $o = new MyClass();
#   $o->id = 4  # sets property throught the +id_set()+ method.
#   $o->id      # returns 4 (using the +id()+ method).
# 
# Note: it is recommended to never call the +property_set+ method
# directly, since it may be renamed someday.
abstract class Misago_Object
{
  private $_mapped_methods = array();
  
  function __get($property)
  {
    # attribute as method
    if (method_exists($this, $property)) {
      return $this->$property();
    }
    
    # default
    return null;
  }
  
  function __set($property, $value)
  {
    # attribute as method
    $method = "{$property}_set";
    if (method_exists($this, $method)) {
      return $this->$method($value);
    }
    
    # default
    return $this->$property = $value;
  }
  
  function __call($method, $args)
  {
    # mapped method?
    if (isset($this->_mapped_methods[$method])) {
      return call_user_func_array($this->_mapped_methods[$method], $args);
    }
    
    # error
    trigger_error("No such method ".get_class($this)."::$method().", E_USER_ERROR);
  }
  
  # Maps an external class methods as instance methods
  # for this class.
  protected function include_module($module)
  {
    $object  = new $module($this);
    $methods = $object->methods;
    if (empty($methods))
    {
      $methods = get_class_methods($module);
      $parent  = get_parent_class($module);
      if (!empty($parent)) {
        $methods = array_diff($methods, get_class_methods($parent));
      }
    }
    
    foreach($methods as $method)
    {
      if (strpos($method, '__') === 0) continue;
      $this->map_method($object, $method, $method);
    }
  }
  
  # Maps a callback as an instance method.
  function map_method($object, $method, $as=null)
  {
    if ($as === null) $as = $method;
    $this->_mapped_methods[$as] = array($object, $method);
  }
  
  /*
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
  */
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
