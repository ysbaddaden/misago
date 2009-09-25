<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class Test_ActiveSupport_Array extends Unit_Test
{
  function test_is_hash()
  {
    $this->assert_false(is_hash(array(1, 2, 3)));
    $this->assert_false(is_hash(array('a', 'b', 'c')));
    $this->assert_true(is_hash(array('a' => 'b', 'c' => 'd')));
    $this->assert_true(is_hash(array(1, 'b' => 2, 3)));
  }
  
  function test_array_collection()
  {
    $this->assert_equal(array_collection("a, b, cde"), array('a', 'b', 'cde'));
    $this->assert_equal(array_collection("a, b, ,cde"), array('a', 'b', 'cde'));
    $this->assert_equal(array_collection(array('a', 'b', 'cde')), array('a', 'b', 'cde'));
    $this->assert_equal(array_collection(array('a', 'b', ' ', ' cde')), array('a', 'b', 'cde'));
  }
  
  function test_hash_merge_recursive()
  {
    $test = hash_merge_recursive(array('a' => "b", 'e' => 'f'), array('a' => "c", 'c' => 'd'));
    $this->assert_equal($test, array('a' => 'c', 'c' => 'd', 'e' => 'f'));

    $test = hash_merge_recursive(array('a' => array('b' => 'c', 'j' => 'k'), 'e' => 'f'), array('a' => array('b' => 'cc'), 'c' => 'd'));
    $this->assert_equal($test, array('a' => array('b' => 'cc', 'j' => 'k'), 'c' => 'd', 'e' => 'f'));
  }
  
  function test_array_sort_recursive()
  {
    $test = array('c', 'b', array('b', 'a'));
    array_sort_recursive($test);
    $this->assert_equal($test, array(array('a', 'b'), 'b', 'c'));
    
    $test = array('c', 'b', array('b' => 'd', 'a' => 'e'));
    array_sort_recursive($test);
    $this->assert_equal($test, array(array('b' => 'd', 'a' => 'e'), 'b', 'c'));
  }
  /*
  function test_linearize_options_tree()
  {
    $includes = array('tags', 'comments' => array('order' => 'created_at asc'));
    $includes = linearize_options_tree($includes);
    $this->assert_equal($includes, array('tags' => array(), 'comments' => array('order' => 'created_at asc')));
    
    $includes = array('tags', 'comments' => array('order' => 'created_at asc', 'include' => array('user')));
    $includes = linearize_options_tree($includes);
    $this->assert_equal($includes, array('tags' => array(), 'comments' => array('order' => 'created_at asc', 'include' => array('user' => array()))));
  }
  */
}

new Test_ActiveSupport_Array();

?>
