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
abstract class Record extends \Misago\Object implements \ArrayAccess, \IteratorAggregate
{
  private $_attributes          = array();
  private $_original_attributes = array();
  
  private   static $_instances = array();
  protected static $_columns = array();
  
  
  function __construct($attributes=null)
  {
    if (!empty($attributes))
    {
      $this->attributes = $attributes;
      $this->reset_original_attributes();
    }
  }
  
  # :private:
  protected static function instance()
  {
    if (!isset(self::$_instances[get_called_class()]))
    {
      $obj = new static();
      self::$_instances[get_called_class()] = $obj;
    }
    return self::$_instances[get_called_class()];
  }
  
  
  function __get($attribute)
  {
    if (static::has_column($attribute))
    {
      return isset($this->_attributes[$attribute]) ?
        $this->_attributes[$attribute] : null;
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
    if (static::has_column($attribute)) {
      return $this->_attributes[$attribute] = $value;
    }
    return parent::__set($attribute, $value);
  }
  
  function __isset($attribute) {
    return isset($this->_attributes[$attribute]);
  }
  
  function __unset($attribute) {
    unset($this->_attributes[$attribute]);
  }
  
  static function columns()
  {
    trigger_error("ActiveRecord\Record::columns() static method must be overwritten by child class.", E_USER_ERROR);
  }
  
  # Checks if a column exists.
  static function has_column($column_name)
  {
    $columns = static::columns();
    return isset($columns[$column_name]);
  }
  
  # Returns an array of column names.
  static function column_names()
  {
    $columns = array_keys(static::columns());
    return $columns;
  }
  
  # Returns the column definition for an attribute;
  function column_for_attribute($attribute)
  {
    $columns = static::columns();
    if (!isset($columns[$attribute])) {
      trigger_error("No such column: '$attribute'.", E_USER_WARNING);
    }
    return $columns[$attribute];
  }
  
  function has_attribute($attribute)
  {
    return array_key_exists($attribute, $this->_attributes);
  }
  
  function attribute_names()
  {
    return array_keys($this->_attributes);
  }
  
  # Returns current attributes.
  function attributes()
  {
    return $this->_attributes;
  }
  
  # :private:
  protected function attributes_set($attributes)
  {
    foreach($attributes as $k => $v) {
      $this->$k = $v;
    }
  }
  
  # :private:
  protected function reset_original_attributes() {
    $this->_original_attributes = $this->_attributes;
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
    $changes = array_diff_assoc($this->_attributes, $this->_original_attributes);
    return $changes;
  }
  
  private function attribute_changed($attribute)
  {
    if (isset($this->_attributes[$attribute])) {
      return ($this->_attributes[$attribute] !== $this->_original_attributes[$attribute]);
    }
    return false;
  }
  
  private function attribute_was($attribute)
  {
    return isset($this->_original_attributes[$attribute]) ?
      $this->_original_attributes[$attribute] : null;
  }
  
  private function attribute_change($attribute)
  {
    if (isset($this->_attributes[$attribute])
      and $this->_attributes[$attribute] !== $this->_original_attributes[$attribute])
    {
      return array($this->_attributes[$attribute], $this->_original_attributes[$attribute]);
    }
    return null;
  }
  
  
  function offsetExists($offset)
  {
    return isset($this->_attributes[$offset]);
  }

  function offsetGet($offset)
  {
    return isset($this->_attributes[$offset]) ? $this->_attributes : null;
  }

  function offsetUnset($offset)
  {
    unset($this->_attributes[$offset]);
  }

  function offsetSet($offset, $value)
  {
    return $this->_attributes[$offset] = $value;
  }

  function getIterator()
  {
    return new \ArrayIterator($this->_attributes);
  }
  
  
  function __sleep()
  {
  	return array_keys($this->_attributes);
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
    $model = \Misago\ActiveSupport\String::underscore(get_class($this));
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
