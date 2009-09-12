<?php

$location = dirname(__FILE__).'/../../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";

# TEST: Attribute & method names must be built from the association's name (ie. category_list, find_with_categories, etc).
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
    
    $post = new Post();
    $rs = $post->tag_list = array('aaa', 'bbb', 'ccc');
    $this->assert_equal("setting tag_list still enables chaining", $rs, array('aaa', 'bbb', 'ccc'));
  }
  
  function test_replace_tags()
  {
    $post = new Post();
    
    $post->tag_list = 'aaa,bbb,ccc';
    $this->assert_equal("casting to array returns the list of tags",
      (array)$post->tag_list, array('aaa', 'bbb', 'ccc'));
    
    $post->tag_list = 'aaa,bbb,ddd';
    $this->assert_equal("the list must have been updated",
      (array)$post->tag_list, array('aaa', 'bbb', 'ddd'));
  }
  
  function test_create_with_tags()
  {
    $post = new Post(array(
      'title'    => 'some title',
      'body'     => 'some body',
      'tag_list' => 'ccc,ddd'
    ));
    $this->assert_true('creating parent creates associated tags', $post->save());
  }
  
  function test_find_with_tags()
  {
    $this->fixtures('posts,tags');
    $post = new Post();
    
    $posts = $post->find_with_tags('php');
    $this->assert_equal("we have a list of posts", $posts->count(), 2);
    $this->assert_instance_of("must be Post objects", $posts[0], 'Post');
    
    $posts = $post->find_with_tags('javascript');
    $this->assert_equal("we got one result", $posts->count(), 1);
    
    $posts = $post->find_with_tags('php,framework');
    $this->assert_equal("we may have multiple tags (matching any)", $posts->count(), 2);
    
    $posts = $post->find_with_tags('php,framework', array('match_all' => true));
    $this->assert_equal("we may have multiple tags (matching all)", $posts->count(), 1);
  }
  
  function test_tag_count()
  {
    $post = new Post();
    
    $this->assert_equal('', $post->tag_count(), array('php' => 2, 'framework' => 1,
      'documentation' => 1, 'emulation' => 1, 'javascript' => 1, 'library' => 1, 'tool' => 1));
    
    $this->assert_equal('', $post->tag_count(array('limit' => 2)), array('php' => 2, 'documentation' => 1));
  }
}

new Test_ActiveRecord_Behaviors_Taggable();

?>
