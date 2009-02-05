<?php

require_once dirname(__FILE__)."/../../lib/unit_test.php";

class FakeClass
{
  
}

class Test_Unit_Test extends Unit_Test
{
  function test_assert_true()
  {
    $this->assert_true('', true);
  }
  
  function test_assert_false()
  {
    $this->assert_false('', false);
  }
  
  function test_assert_equal()
  {
    $this->assert_equal('strings', 'a', 'a');
    $this->assert_equal('integers', 5, 5);
    $this->assert_equal('single dimension arrays', array('a', 'b', 'c'), array('a', 'b', 'c'));
    $this->assert_equal('multidimensional arrays', array('a', array('b', 'c'), 'd'), array('a', array('b', 'c'), 'd'));
    $this->assert_equal('multidimensional associative arrays', array('az' => array('a', 'b')), array('az' => array('a', 'b')));
    
    $this->assert_equal("hash containing null values", array('a' => null), array('a' => null));
  }
  
  function test_assert_not_equal()
  {
    $this->assert_not_equal('strings', 'a', 'b');
    $this->assert_not_equal('integers', 5, 6);
    $this->assert_not_equal('single dimension arrays', array('a', 'b', 'c'), array('a', 'd', 'b'));
    $this->assert_not_equal('multidimensional arrays', array('a', array('b', 'c'), 'd'), array('a', array('c', 'b'), 'e'));
    $this->assert_not_equal('multidimensional associative arrays (keys)', array('az' => array('a', 'b')), array('bz' => array('a', 'b')));
    $this->assert_not_equal('multidimensional associative arrays (values)', array('az' => array('a', 'b')), array('az' => array('d', 'e')));
  }
  
  function test_assert_type()
  {
    $this->assert_equal('testing integer', gettype(1), 'integer');
    $this->assert_equal('testing object', gettype(new FakeClass()), 'object');
  }
}

new Test_Unit_Test();

?>
