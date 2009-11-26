<?php
namespace Misago;

# Generic object, to share methods between all misago's classes.
#
# Example class:
#
#   class MyClass extends Misago\Object
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
abstract class Object
{
  private static $_included_modules = array();
  private static $_mapped_statics   = array();
  private        $_mapped_methods   = array();
  
  function __construct()
  {
    if (!empty(self::$_included_modules[get_called_class()]))
    {
      foreach(self::$_included_modules[get_called_class()] as $module => $methods)
      {
        $object = new $module($this);
        foreach($methods as $method) {
          $this->map_method(array($object, $method), $method);
        }
      }
    }
  }
  
  static function __constructStatic()
  {
    
  }
  
  function __get($property)
  {
    if (method_exists($this, $property)
      or isset($this->_mapped_methods[$property]))
    {
      return $this->$property();
    }
    return null;
  }
  
  function __set($property, $value)
  {
    $method = "{$property}_set";
    if (method_exists($this, $method)
      or isset($this->_mapped_methods[$method]))
    {
      return $this->$method($value);
    }
    return $this->$property = $value;
  }
  
  function __call($method, $args)
  {
    if (isset($this->_mapped_methods[$method])) {
      return call_user_func_array($this->_mapped_methods[$method], $args);
    }
    trigger_error("No such method ".get_class($this)."::$method().", E_USER_ERROR);
  }
  
  static function __callStatic($method, $args)
  {
    if (isset(self::$_mapped_statics[get_called_class()][$method])) {
      return forward_static_call_array(self::$_mapped_statics[get_called_class()][$method], $args);
    }
    trigger_error("No such static method ".get_called_class()."::$method().", E_USER_ERROR);
  }
  
  # Maps instance and static methods from a given class, as instance
  # and static methods for this class.
  protected static function include_module($module)
  {
    $reflection = new \ReflectionClass($module);
    
    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | !\ReflectionMethod::IS_STATIC);
    if (!empty($methods))
    {
      self::$_included_modules[get_called_class()][$module] = array();
      foreach($methods as $method)
      {
        if (strpos($method->name, '__') === 0 or $method->class != $module) {
          continue;
        }
        if (method_exists(get_called_class(), $method->name)) {
          trigger_error("Method ".get_called_class()."->{$method->name}() is already defined.", E_USER_WARNING);
        }
        self::$_included_modules[get_called_class()][$module][] = $method->name;
      }
    }
    
    $statics = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);
    foreach($statics as $method)
    {
      if (strpos($method->name, '__') === 0 or $method->class != $module) {
        continue;
      }
      if (method_exists(get_called_class(), $method->name)) {
        trigger_error("Method ".get_called_class()."::{$method->name}() is already defined.", E_USER_WARNING);
      }
      static::map_static_method(array($module, $method->name), $method->name);
    }
  }
  
  # Maps an external callback as class method.
  function map_method($callback, $as) {
    $this->_mapped_methods[$as] =& $callback;
  }
  
  # Maps an external callback as class static method.
  static function map_static_method($callback, $as) {
    self::$_mapped_statics[get_called_class()][$as] =& $callback;
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
