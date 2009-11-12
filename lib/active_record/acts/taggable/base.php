<?php

class ActiveRecord_Acts_Taggable_Base extends ActiveRecord_Acts_Taggable_Abstract
{
  protected $assoc = 'tags';
  
  function tag_list() {
    return $this->tag_list;
  }
  
  function tag_list_set($value) {
    return $this->tag_list = $value;
  }
  
  function find_with_tags($tags, $options=array()) {
    return $this->tag_list->find_tagged_with($tags, $options);
  }
  
  function count_with_tags($tags, $options=array()) {
    return $this->tag_list->count_tagged_with($tags, $options);
  }
  
  function tag_count($options=array()) {
    return $this->tag_list->tag_count($options);
  }
}

?>
