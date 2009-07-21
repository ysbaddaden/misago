<?php

# Generic object, to share methods between all misago's classes.
abstract class Object
{
  protected $attr_read = array();
  
  function __get($attr)
  {
    if (in_array($attr, $this->attr_read)) {
      return $this->$attr;
    }
    return null;
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
