<?php

abstract class ActiveRecord_Acts_Taggable_Abstract extends Misago_Object
{
  protected $assoc;
  private   $_tag_list;
  
  function __construct($parent)
  {
    $this->parent = $parent;
  }
  
  function __get($property)
  {
    if ($property == 'tag_list')
    {
      if (!isset($this->_tag_list))
      {
        $this->_tag_list = new ActiveRecord_Acts_Taggable_TagList(
          $this->parent, $this->parent->association($this->assoc));
      }
      return $this->_tag_list;
    }
    return null;
  }
  
  function __set($property, $value)
  {
    if ($property == 'tag_list') {
      return $this->tag_list->set($value);
    }
    return $this->$property = $value;
  }
}

?>
