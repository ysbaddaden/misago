<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../test/test_app/config/boot.php';

class Test_ActiveRecord_Calculations extends Unit_TestCase
{
  function test_simple_count()
  {
    $post = new Post();
    $this->assert_equal('no data', $post->count(), 0);
    
    $this->fixtures('posts,tags');
    $this->assert_equal('with data', $post->count(), 3);
    
    $value = $post->count('posts.id', array(
      'joins'      => 'tags',
      'conditions' => array('tags.tag' => 'php'),
      'distinct'   => true,
    ));
    $this->assert_equal('with association and conditions', $value, 2);
  }
}
new Test_ActiveRecord_Calculations();

?>
