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
#   $o->new_record;         # => returns false
#
# You may also have a write setter, using a +property_set+ method:
# 
#   $o = new MyClass();
#   $o->id = 4;             # => returns 4
# 
abstract class Object
{
  static function __constructStatic() { }
  protected $_reflection;
  
  function __get($property) {
    return method_exists($this, $property) ? $this->$property() : null;
  }
  
  function __set($property, $value)
  {
    $method = "{$property}_set";
    if (method_exists($this, $method)) {
      return $this->$method($value);
    }
    
    # if the property exists, I wouldn't be there I the caller had 
    # direct access to it, it's thus propected or private!
    if (!isset($this->_reflection)) {
      $this->_reflection = new \ReflectionClass(get_called_class());
    }
    if ($this->_reflection->hasProperty($property)) {
      throw new \Exception("Can't access property $property which is either protected or private.");
    }
    
    $this->$property = $value;
  }
  
  function to_s() {
    return $this->__toString();
  }
  
  function to_xml() {
    return $this->to_s();
  }

  function to_json() {
    return $this->to_s();
  }
  
  function to_yaml() {
    return $this->to_s();
  }
  
  function to_array() {
    return $this->to_s();
  }
}

?>
