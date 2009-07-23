<?php

$location = __DIR__.'/../../../../..';
$_SERVER['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Behaviors_Taggable extends Unit_TestCase
{
  function test_tag_list()
  {
    $post = new Post();
    $this->assert_instance_of('post->tag_list exists and is an instance of TagList',
      $post->tag_list, 'ActiveRecord_Behaviors_Taggable_TagList');
  }
  
  function test_get_tag_list_when_parent_is_new_record()
  {
    $post = new Post();
    $this->assert_equal('tag_list is empty', (array)$post->tag_list, array());
  }
  
  function test_set_tag_list_when_parent_is_new_record()
  {
    $post = new Post();
    
    $rs = $post->tag_list = 'aaa,ddd, bbb, ';
    $this->assert_equal("setting tag_list still enables chaining", $rs, 'aaa,ddd, bbb, ');
    $this->assert_equal("tag_list now contains values", (array)$post->tag_list, array('aaa', 'bbb', 'ddd'));
    
#    $rs = $post->tag_list = array('aaa', 'bbb', 'ccc');
#    $this->assert_equal("setting tag_list still enables chaining", $rs, 'aaa,bbb, ddd');
  }
}

new Test_ActiveRecord_Behaviors_Taggable();

?>
