<?php

class ActiveRecord_Collection extends ArrayObject
{
  public $model;
  
  function __construct($input, $model=null)
  {
    parent::__construct($input);
    $this->model = $model;
  }
  
  # Exports to a JSON string.
  function to_json()
  {
    $data = array();
    foreach($this as $v) {
      $data[] = $v->to_json();
    }
    return json_encode($data);
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
}

?>
