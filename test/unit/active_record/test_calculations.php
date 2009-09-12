<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../test/test_app/config/boot.php';

class Test_ActiveRecord_Calculations extends Unit_TestCase
{
  function test_simple_count()
  {
    $this->fixtures('posts,tags,products');
    
    $post = new Post();
    $this->assert_equal('with data', $post->count(), '3');
    $this->assert_equal('with column name', $post->count('id'), '3');
    
    $value = $post->count(array(
      'joins'      => 'tags',
      'conditions' => array('tag' => array('php', 'framework')),
    ));
    $this->assert_equal('with options', $value, '3');
    
    $value = $post->count('posts.id', array(
      'joins'      => 'tags',
      'conditions' => array('tag' => array('php', 'framework')),
      'distinct'   => true,
    ));
    $this->assert_equal('with options and distinct column name', $value, '2');
  }
  
  function test_maximum()
  {
    $post = new Post();
    $this->assert_equal('', $post->maximum('id'), '3');
    
    $product = new Product();
    $this->assert_equal('', round($product->maximum('price'), 2), 9.99);
    
    $value = $product->minimum('price', array('conditions' => 'id in (1, 3)'));
    $this->assert_equal('with options', round($value, 2), 6.95);
  }
  
  function test_minimum()
  {
    $post = new Post();
    $this->assert_equal('', $post->minimum('id'), '1');
    
    $product = new Product();
    $this->assert_equal('', round($product->minimum('price'), 2), 4.98);
    
    $value = $product->minimum('price', array('conditions' => 'id in (1, 3)'));
    $this->assert_equal('with options', round($value, 2), 6.95);
  }
  
  function test_average()
  {
    $product = new Product();
    $this->assert_equal('', (float)$product->average('id'), 2.0);
    $this->assert_equal('', round($product->average('price'), 2), 7.31);
    
    $value = $product->average('price', array('conditions' => 'in_stock is not null'));
    $this->assert_equal('with options', round($value, 2), 5.96);
  }
  
  function test_sum()
  {
    $product = new Product();
    $this->assert_equal('', $product->sum('id'), '6');
    $this->assert_equal('', round($product->sum('price'), 2), 21.92);
    
    $value = $product->sum('price', array('conditions' => 'in_stock is null'));
    $this->assert_equal('with options', round($value, 2), 9.99);
  }
}
new Test_ActiveRecord_Calculations();

?>
