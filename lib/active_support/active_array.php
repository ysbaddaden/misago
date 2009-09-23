<?php

# TEST: Write tests!
# @namespace ActiveSupport
class ActiveArray extends ArrayObject
{
  public $model;
  
  function __construct($ary, $model=null)
  {
    parent::__construct($ary);
    $this->model = $model;
  }
  
  function __get($attr)
  {
    if ($attr == 'klass')
    {
      $klass = $this->model;
      return $this->klass = new $klass;
    }
    trigger_error('Unknown attribute: '.get_class($this).'::'.$attr.'.', E_USER_WARNING);
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
}

?>
