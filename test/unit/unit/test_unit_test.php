<?php
require_once __DIR__.'/../../unit.php';

class FakeClass  { }
class FakeClass2 { }
class FakeException  extends Exception { }
class FakeException2 extends Exception { }

class Test_Test_Unit_TestCase extends Test\Unit\TestCase
{
  function test_assert_true()
  {
    $this->assert_true(true);
  }
  
  function test_assert_false()
  {
    $this->assert_false(false);
  }
  
  function test_assert_null()
  {
    $this->assert_null(null);
  }
  
  function test_assert_not_null()
  {
    $this->assert_not_null(1);
    $this->assert_not_null(false);
    $this->assert_not_null(array());
    $this->assert_not_null(0);
  }
  
  function test_assert_equal()
  {
    $this->assert_equal('a', 'a');
    $this->assert_equal(5, 5);
    
    $this->assert_equal(array('a', 'b', 'c'), array('a', 'b', 'c'));
    $this->assert_equal(array('a', array('b', 'c'), 'd'), array('a', array('b', 'c'), 'd'));
    $this->assert_equal(array('a' => 'b', 'c' => 'd'), array('a' => 'b', 'c' => 'd'), 'hash in order');
    $this->assert_equal(array('c' => 'd', 'a' => 'b'), array('a' => 'b', 'c' => 'd'), 'hash in disorder');
    $this->assert_equal(array('az' => array('a', 'b')), array('az' => array('a', 'b')));

#    $this->assert_equal(array('a', 'c', 'b'), array('c', 'b', 'a'), 'unordered arrays');
#    $this->assert_equal(array(array('a', 'b'), 'c', 'b'), array('c', 'b', array('b', 'a')), 'multidimensional unordered arrays');
#    $this->assert_equal(array(array('a' => 'e'), 'c', 'b'), array('c', 'b', array('a' => 'e')), 'multidimensional unordered arrays mixed with hashes');
    
    $this->assert_equal(array('a' => null), array('a' => null), "hash containing null values");
    
    $a = new FakeClass(); $a->id = 1; $a->price = 9.95; $a->name = 'azerty';
    $b = new FakeClass(); $b->id = 1; $b->price = 9.95; $b->name = 'azerty';
    $this->assert_equal($a, $b, "objects");
    $this->assert_equal(array($a, $b), array($a, $b), "array of objects");
  }
  
  function test_assert_not_equal()
  {
    $this->assert_not_equal('a', 'b');
    $this->assert_not_equal(5, 6);
    
    $this->assert_not_equal(array('a', 'b', 'c'), array('a', 'd', 'b'));
    $this->assert_not_equal(array('a', array('b', 'c'), 'd'), array('a', array('c', 'b'), 'e'));
    
    $this->assert_not_equal(array('a' => 'c', 'c' => 'd'), array('a' => 'b', 'c' => 'd'), 'hash in order');
    $this->assert_not_equal(array('c' => 'e', 'a' => 'b'), array('a' => 'b', 'c' => 'd'), 'hash in disorder');
    $this->assert_not_equal(array('az' => array('a', 'b')), array('bz' => array('a', 'b')), 'multidimensional hash (keys)');
    $this->assert_not_equal(array('az' => array('a', 'b')), array('az' => array('d', 'e')), 'multidimensional hash arrays (values)');
    
    $a = new FakeClass(); $a->id = 1; $a->price = 9.95; $a->name = 'azerty';
    $b = new FakeClass(); $b->id = 1; $b->price = 10.95; $b->name = 'azerty';
    $this->assert_not_equal($a, $b, "objects with different values");
    
    $a = new FakeClass(); $a->id = 1; $a->price = 9.95; $a->name = 'azerty';
    $b = new FakeClass(); $b->id = 1; $b->retail_price = 10.95; $b->name = 'azerty';
    $this->assert_not_equal($a, $b, "objects with different keys");
    
    $a = new FakeClass();
    $b = new FakeClass2();
    $this->assert_not_equal($a, $b, "different classes");
    
    $a = new FakeClass(); $a->id = 1; $a->price = 9.95; $a->name = 'azerty';
    $b = new FakeClass(); $b->id = 1; $b->price = 9.95; $b->name = 'azerty';
    $c = new FakeClass2();
    $this->assert_not_equal(array($a, $b), array($a, $c), "array of objects");
  }
  
  function test_assert_type()
  {
    $this->assert_type(1, 'integer');
    $this->assert_type('1', 'string');
    $this->assert_type(new FakeClass(), 'object');
  }
  
  function test_assert_instance_of()
  {
    $this->assert_instance_of(new FakeClass(), 'FakeClass');
  }
  
  function test_match()
  {
    $this->assert_match('/toto/', 'toto wakes up');
    $this->assert_no_match('/tata/', 'toto wakes up');
  }
  
  function test_assert_thrown()
  {
    $this->assert_throws('FakeException', function() {
      throw new FakeException();
    });
    $this->assert_throws('FakeException', 'FakeException2', function() {
      throw new FakeException2();
    }, 'a message');
  }
  
  function test_nothing_thrown()
  {
    $this->assert_nothing_thrown(function() {});
    $this->assert_nothing_thrown(function() { return false; }, 'a message');
  }
}

?>
