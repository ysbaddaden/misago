<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../../test/test_app/config/boot.php';

class Test_ActiveRecord_Acts_Taggable extends Unit_TestCase
{
  function test_tag_list()
  {
    $post = new Post();
    $this->assert_instance_of($post->tag_list, 'ActiveRecord_Acts_Taggable_TagList');
    
    # assigning & casting
    $post->tag_list = array('aaa', 'bbb', 'ccc');
    $this->assert_equal((string)$post->tag_list, 'aaa, bbb, ccc');
    $this->assert_equal((array)$post->tag_list, array('aaa', 'bbb', 'ccc'));
  }
  
  function test_get_tag_list_when_parent_is_new_record()
  {
    $post = new Post();
    $this->assert_equal((array)$post->tag_list, array());
  }
  
  function test_set_tag_list_when_parent_is_new_record()
  {
    $post = new Post();
    
    $tags = $post->tag_list = 'aaa,ddd, bbb, ';
    $this->assert_equal($tags, 'aaa,ddd, bbb, ', "setting tag_list still enables chaining");
    $this->assert_equal((array)$post->tag_list, array('aaa', 'bbb', 'ddd'), "tag_list now contains values");
    
    $post = new Post();
    $tags = $post->tag_list = array('aaa', 'bbb', 'ccc');
    $this->assert_equal($tags, array('aaa', 'bbb', 'ccc'), "setting tag_list still enables chaining");
  }
  
  function test_replace_tags()
  {
    $post = new Post();
    
    $post->tag_list = 'aaa,bbb,ccc';
    $this->assert_equal((array)$post->tag_list, array('aaa', 'bbb', 'ccc'), "casting to array returns the list of tags");
    
    $post->tag_list = 'aaa,bbb,ddd';
    $this->assert_equal((array)$post->tag_list, array('aaa', 'bbb', 'ddd'), "the list must have been updated");
  }
  
  function test_add_remove_methods()
  {
    $post = new Post();
    $post->tag_list = 'aaa,bbb,ccc';
    
    $post->tag_list->remove('bbb');
    $this->assert_equal((string)$post->tag_list, 'aaa, ccc');
    
    $post->tag_list->add('aze');
    $this->assert_equal((string)$post->tag_list, 'aaa, aze, ccc');
    
    $post->tag_list->remove('unknown_tag');
    $this->assert_equal((string)$post->tag_list, 'aaa, aze, ccc');
    
    $post->tag_list->add('aaa');
    $this->assert_equal((string)$post->tag_list, 'aaa, aze, ccc');
  }
  
  function test_create_with_tags()
  {
    $post = new Post(array(
      'title'    => 'some title',
      'body'     => 'some body',
      'tag_list' => 'ccc,ddd'
    ));
    $this->assert_true($post->save(), 'creating parent creates associated tags');
  }
  
  function test_find_with_tags()
  {
    $this->fixtures('posts', 'tags');
    $post = new Post();
    
    $posts = $post->find_with_tags('php');
    $this->assert_equal($posts->count(), 2, "we have a list of posts");
    $this->assert_instance_of($posts[0], 'Post', "must be Post objects");
    
    $posts = $post->find_with_tags('javascript');
    $this->assert_equal($posts->count(), 1, "we got one result");
    
    $posts = $post->find_with_tags('php,framework');
    $this->assert_equal($posts->count(), 2, "we may have multiple tags (matching any)");
    
    $posts = $post->find_with_tags('php,framework', array('match_all' => true));
    $this->assert_equal($posts->count(), 1, "we may have multiple tags (matching all)");
  }
  
  function test_count_with_tags()
  {
    $post = new Post();
    
    $count = $post->count_with_tags('php');
    $this->assert_equal($count, 2);
    
    $count = $post->count_with_tags('javascript');
    $this->assert_equal($count, 1);
    
    $count = $post->count_with_tags('php,framework');
    $this->assert_equal($count, 2, "we may have multiple tags (matching any)");
    
    $count = $post->count_with_tags('php,framework', array('match_all' => true));
    $this->assert_equal($count, 1, "we may have multiple tags (matching all)");
  }
  
  function test_tag_count()
  {
    $post = new Post();
    
    $this->assert_equal($post->tag_count(), array('php' => 2, 'framework' => 1,
      'documentation' => 1, 'emulation' => 1, 'javascript' => 1, 'library' => 1, 'tool' => 1));
    
    $this->assert_equal($post->tag_count(array('limit' => 2)), array('php' => 2, 'documentation' => 1));
  }
}

new Test_ActiveRecord_Acts_Taggable();

?>
