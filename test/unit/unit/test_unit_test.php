<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../test_app/config/boot.php";

class FakeClass  { }
class FakeClass2 { }

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
  
  function test_assert_null()
  {
    $this->assert_null('', null);
  }
  
  function test_assert_equal()
  {
    $this->assert_equal('strings', 'a', 'a');
    $this->assert_equal('integers', 5, 5);
    
    $this->assert_equal('arrays', array('a', 'b', 'c'), array('a', 'b', 'c'));
    $this->assert_equal('multidimensional arrays',
      array('a', array('b', 'c'), 'd'), array('a', array('b', 'c'), 'd'));
    $this->assert_equal('hash in order', array('a' => 'b', 'c' => 'd'), array('a' => 'b', 'c' => 'd'));
    $this->assert_equal('hash in disorder', array('c' => 'd', 'a' => 'b'), array('a' => 'b', 'c' => 'd'));
    $this->assert_equal('multidimensional hash',
      array('az' => array('a', 'b')), array('az' => array('a', 'b')));

    $this->assert_equal('unordered arrays', array('a', 'c', 'b'), array('c', 'b', 'a'));
    $this->assert_equal('multidimensional unordered arrays',
      array(array('a', 'b'), 'c', 'b'), array('c', 'b', array('b', 'a')));
    $this->assert_equal('multidimensional unordered arrays mixed with hashes',
      array(array('a' => 'e'), 'c', 'b'), array('c', 'b', array('a' => 'e')));
    
    $this->assert_equal("hash containing null values", array('a' => null), array('a' => null));
    
    $a = new FakeClass(); $a->id = 1; $a->price = 9.95; $a->name = 'azerty';
    $b = new FakeClass(); $b->id = 1; $b->price = 9.95; $b->name = 'azerty';
    $this->assert_equal("objects", $a, $b);
    $this->assert_equal("array of objects", array($a, $b), array($a, $b));
  }
  
  function test_assert_not_equal()
  {
    $this->assert_not_equal('strings', 'a', 'b');
    $this->assert_not_equal('integers', 5, 6);
    
    $this->assert_not_equal('arrays', array('a', 'b', 'c'), array('a', 'd', 'b'));
    $this->assert_not_equal('multidimensional arrays',
      array('a', array('b', 'c'), 'd'), array('a', array('c', 'b'), 'e'));
    
    $this->assert_not_equal('hash in order',    array('a' => 'c', 'c' => 'd'), array('a' => 'b', 'c' => 'd'));
    $this->assert_not_equal('hash in disorder', array('c' => 'e', 'a' => 'b'), array('a' => 'b', 'c' => 'd'));
    $this->assert_not_equal('multidimensional hash (keys)',
      array('az' => array('a', 'b')), array('bz' => array('a', 'b')));
    $this->assert_not_equal('multidimensional hash arrays (values)',
      array('az' => array('a', 'b')), array('az' => array('d', 'e')));
    
    $a = new FakeClass(); $a->id = 1; $a->price = 9.95; $a->name = 'azerty';
    $b = new FakeClass(); $b->id = 1; $b->price = 10.95; $b->name = 'azerty';
    $this->assert_not_equal("objects with different values", $a, $b);
    
    $a = new FakeClass(); $a->id = 1; $a->price = 9.95; $a->name = 'azerty';
    $b = new FakeClass(); $b->id = 1; $b->retail_price = 10.95; $b->name = 'azerty';
    $this->assert_not_equal("objects with different keys", $a, $b);
    
    $a = new FakeClass();
    $b = new FakeClass2();
    $this->assert_not_equal("different classes", $a, $b);
    
    $a = new FakeClass(); $a->id = 1; $a->price = 9.95; $a->name = 'azerty';
    $b = new FakeClass(); $b->id = 1; $b->price = 9.95; $b->name = 'azerty';
    $c = new FakeClass2();
    $this->assert_not_equal("array of objects", array($a, $b), array($a, $c));
  }
  
  function test_assert_type()
  {
    $this->assert_type('testing integer', 1, 'integer');
    $this->assert_type('testing string', '1', 'string');
    $this->assert_type('testing object', new FakeClass(), 'object');
  }
  
  function test_assert_instance_of()
  {
    $this->assert_instance_of('', new FakeClass(), 'FakeClass');
  }
  
  function test_match()
  {
    $this->assert_match('', '/toto/', 'toto wakes up');
    $this->assert_no_match('', '/tata/', 'toto wakes up');
  }
}

new Test_Unit_Test();

?>
