<?php
namespace Misago\ActiveSupport;

class ActiveArray implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
  protected $ary;
  public    $model;
  
  function __construct($ary, $model=null)
  {
    $this->ary   = $ary;
    $this->model = $model;
  }
  
  function __get($attr)
  {
    if ($attr == 'klass')
    {
      $klass = $this->model;
      return $this->klass = new $klass;
    }
    trigger_error('Unknown attribute: '.get_called_class().'->'.$attr.'.', E_USER_WARNING);
  }
  
  # Exports to a JSON string.
  function to_json()
  {
    return json_encode($this->to_array());
  }
  
  # Exports to an XML string.
  function to_xml()
  {
    $xml = '';
    foreach($this as $v) {
      $xml .= $v->to_xml();
    }
    $plural = String::pluralize(String::underscore($this->model));
    return "<$plural>$xml</$plural>";
  }
  
  # Exports as array.
  function to_array()
  {
    $ary = array();
    foreach($this as $v) {
      $ary[] = $v->to_array();
    }
    return $ary;
  }
  
  # :nodoc:
  function castArray() {
    return $this->ary;
  }
  
  function offsetExists($key) {
    return isset($this->ary[$key]);
  }
  
  function offsetSet($key, $value)
  {
    if ($key === null) {
      return $this->ary[] = $value;
    }
    return $this->ary[$key] = $value;
  }
  
  function offsetGet($key) {
    return isset($this->ary[$key]) ? $this->ary[$key] : null;
  }
  
  function offsetUnset($key) {
    unset($this->ary[$key]);
  }
  
  function getIterator() {
    return new \ArrayIterator($this->ary);
  }
  
  function count() {
    return count($this->ary);
  }
  
  function serialize() {
    return serialize($this->ary);
  }
  
  function unserialize($serialized) {
    $this->data = unserialize($serialized);
  }
  
  function append($value) {
    array_push($this->ary, $value);
  }
  
  function exchangeArray($input)
  {
    if (is_object($input))
    {
      $this->ary = array();
      foreach($input as $key => $value) {
        $this->ary[$key] = $value;
      }
    }
    else {
      $this->ary = $input;
    }
  }
  
  function asort() {
    asort($this->ary);
  }
  
  function ksort() {
    ksort($this->ary);
  }
  
  function uasort($callback) {
    uasort($this->ary, $callback);
  }
  
  function uksort($callback) {
    uksort($this->ary, $callback);
  }
  
  function natcasesort() {
    natcasesort($this->ary);
  }
  
  function natsort() {
    natsort($this->ary);
  }
}

?>
