<?php

$_ENV['MISAGO_ENV'] = 'test';
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class Test_ActiveSupport_Array extends Unit_Test
{
  function test_is_hash()
  {
    $this->assert_false("array of integers", is_hash(array(1, 2, 3)));
    $this->assert_false("array of strings", is_hash(array('a', 'b', 'c')));
    $this->assert_true("full hash", is_hash(array('a' => 'b', 'c' => 'd')));
    $this->assert_true("a mixup is a hash", is_hash(array(1, 'b' => 2, 3)));
  }
  
  function test_array_collection()
  {
    $this->assert_equal("string collection", array_collection("a, b, cde"), array('a', 'b', 'cde'));
    $this->assert_equal("string collection with empty values", array_collection("a, b, ,cde"), array('a', 'b', 'cde'));
    $this->assert_equal("array", array_collection(array('a', 'b', 'cde')), array('a', 'b', 'cde'));
    $this->assert_equal("array with empty values", array_collection(array('a', 'b', ' ', ' cde')), array('a', 'b', 'cde'));
  }
  
  function test_hash_merge_recursive()
  {
    $test = hash_merge_recursive(array('a' => "b", 'e' => 'f'), array('a' => "c", 'c' => 'd'));
    $this->assert_equal("one dimension", $test, array('a' => 'c', 'c' => 'd', 'e' => 'f'));

    $test = hash_merge_recursive(array('a' => array('b' => 'c', 'j' => 'k'), 'e' => 'f'), array('a' => array('b' => 'cc'), 'c' => 'd'));
    $this->assert_equal("two dimensions", $test, array('a' => array('b' => 'cc', 'j' => 'k'), 'c' => 'd', 'e' => 'f'));
  }
  
  function test_array_sort_recursive()
  {
    $test = array('c', 'b', array('b', 'a'));
    array_sort_recursive($test);
    $this->assert_equal("one dimension", $test, array(array('a', 'b'), 'b', 'c'));
    
    $test = array('c', 'b', array('b' => 'd', 'a' => 'e'));
    array_sort_recursive($test);
    $this->assert_equal("one dimension", $test, array(array('b' => 'd', 'a' => 'e'), 'b', 'c'));
  }
}

new Test_ActiveSupport_Array();

?>
