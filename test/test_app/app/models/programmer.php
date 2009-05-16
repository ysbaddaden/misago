<?php

class Programmer extends ActiveRecord_Base
{
  protected $has_and_belongs_to_many = array('projects');
  
  function test_merge_conditions($a, $b) {
    return $this->merge_conditions($a, $b);
  }
  
  function test_merge_options($a, $b) {
    return $this->merge_options($a, $b);
  }
}

?>
