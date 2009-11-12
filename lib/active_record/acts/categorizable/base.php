<?php

class ActiveRecord_Acts_Categorizable_Base extends ActiveRecord_Acts_Taggable_Abstract
{
  protected $assoc = 'categories';
  
  function category_list() {
    return $this->tag_list;
  }
  
  function category_list_set($value) {
    return $this->tag_list = $value;
  }
  
  function find_with_categories($categories, $options=array()) {
    return $this->tag_list->find_tagged_with($categories, $options);
  }
  
  function count_with_categories($categories, $options=array()) {
    return $this->tag_list->count_tagged_with($categories, $options);
  }
  
  function category_count($categories, $options=array()) {
    return $this->tag_list->tag_count($categories, $options);
  }
}

?>
