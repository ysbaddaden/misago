<?php
namespace Misago\ActiveRecord;

# Generic Record.
# 
# - A record is a list of attributes.
# - You may iterate throught attributes (like an array).
# - You may serialize it's contents to PHP (thought serialize), XML or JSON.
# 
# =Dirty objects
#
# You may track attribute changes.
# 
# Has a model changed?
# 
#   $product = new Product(array('title' => 'qwerty'));;
#   $product->changed          # => false
# 
# Change an attribute:
# 
#   $product->title = 'azerty'
#   $product->changed          # => true
#   $product->title_changed    # => true
#   $product->title_was        # => 'qwerty'
#   $product->title_change     # => array('qwerty', 'azerty')
#   $product->title = 'swerty'
#   $product->title_change     # => array('qwerty', 'swerty')
# 
# Saving resets contexts:
# 
#   $product->save()
#   $product->changed          # => false
#   $product->title_changed    # => false
# 
# Assigning identical values keeps the record unchanged.
# 
#   $product->title = 'swerty'
#   $product->changed          # => false
#   $product->title_changed    # => false
#   $product->title_change     # => null
abstract class Record extends \Misago\Object implements \Iterator
{
  protected $columns               = array();
  protected $__attributes          = array();
  protected $__original_attributes = array();
  protected $new_record            = true;
  
  function __construct($attributes=null)
  {
    if (!empty($attributes))
    {
      $this->set_attributes($attributes);
      $this->__original_attributes = $this->__attributes;
    }
  }
  
  function new_record() {
    return $this->new_record;
  }
  
  # Sets record's attributes.
  function set_attributes($arg)
  {
    foreach($arg as $attribute => $value) {
      $this->$attribute = $value;
    }
  }
  
  # Returns current attributes.
  function attributes()
  {
    return $this->__attributes;
  }
  
  # List of attributes with unsaved changes.
  function & changed()
  {
    $changes = $this->changes();
    $changed = array_keys($changes);
    return $changed;
  }
  
  # Returns the list of changed attributes, with associated values.
  function & changes()
  {
    $changes = array_diff_assoc($this->__attributes, $this->__original_attributes);
    return $changes;
  }
  
  private function attribute_changed($attribute)
  {
    if (isset($this->__attributes[$attribute])) {
      return ($this->__attributes[$attribute] !== $this->__original_attributes[$attribute]);
    }
    return false;
  }
  
  private function attribute_was($attribute)
  {
    return isset($this->__original_attributes[$attribute]) ?
      $this->__original_attributes[$attribute] : null;
  }
  
  private function attribute_change($attribute)
  {
    if (isset($this->__attributes[$attribute])
      and $this->__attributes[$attribute] !== $this->__original_attributes[$attribute])
    {
      return array($this->__attributes[$attribute], $this->__original_attributes[$attribute]);
    }
    return null;
  }
  
  
  function __get($attribute)
  {
    if (isset($this->columns[$attribute])) {
      return isset($this->__attributes[$attribute]) ? $this->__attributes[$attribute] : null;
    }
    elseif ($attribute == 'changed')
    {
      $changes = $this->changes();
      return (!empty($changes));
    }
    elseif (preg_match('/^(.+)_(changed|was|change)$/', $attribute, $match))
    {
      $func = 'attribute_'.$match[2];
      return $this->$func($match[1]);
    }
    return parent::__get($attribute);
  }
  
  function __set($attribute, $value)
  {
    if (isset($this->columns[$attribute])) {
      return $this->__attributes[$attribute] = $value;
    }
    return parent::__set($attribute, $value);
  }
  
  function __isset($attribute) {
    return isset($this->__attributes[$attribute]);
  }
  
  function __unset($attribute) {
    unset($this->__attributes[$attribute]);
  }
  /*
  function __call($func, $args)
  {
    $class = get_class($this);
    trigger_error("No such method: $class::$func().", E_USER_ERROR);
  }
  */
  
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
      switch(gettype($this->$k))
      {
        case 'string': $v = "<![CDATA[".$this->$k."]]>"; break;
        case 'object': $v = $this->$k->to_xml(); break;
        default:       $v = $this->$k;
      }
      $xml .= "<$k>$v</$k>";
    }
    $model = String::underscore(get_class($this));
    return "<$model>$xml</$model>";
  }
  
  # Exports the record as JSON.
  function to_json()
  {
  	return json_encode($this->to_array());
  }
  
  # Exports the record as YAML.
  function to_yaml()
  {
    $attributes = $this->__sleep();
    $data = array();
    foreach($attributes as $k) {
    	$data[$k] = is_object($this->$k) ? $this->$k->to_yaml() : $this->$k;
    }
    return yaml_encode($data);
  }
  
  # Exports the record as array.
  function to_array()
  {
    $attributes = $this->__sleep();
  	$ary = array();
    foreach($attributes as $k) {
    	$ary[$k] = is_object($this->$k) ? $this->$k->to_array() : $this->$k;
    }
    return $ary;
  }
}

?>
