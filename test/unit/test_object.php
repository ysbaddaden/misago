<?php
require_once __DIR__.'/../unit.php';

class SomeObject extends Misago\Object
{
  protected $new_record  = false;
  protected $table_name  = 'some_objects';
  protected $primary_key = 'private';
  
  private   $id          = 'a';
  private   $restricted  = true;
  
  function id() {
    return $this->id;
  }
  
  function id_set($id) {
    return $this->id = $id;
  }
  
  function new_record() {
    return $this->new_record;
  }
  
  function table_name() {
    return $this->table_name;
  }
}

class Test_Object extends Test\Unit\TestCase
{
  function test_attributes_as_methods()
  {
    $o = new SomeObject();
    
    $this->assert_false($o->new_record);
    $this->assert_equal($o->table_name, 'some_objects');
    $this->assert_null($o->primary_key);
    $this->assert_null($o->unknown_property);
    
    $this->assert_equal($o->id, 'a');
    $this->assert_equal($o->id = 'b', 'b');
    $this->assert_equal($o->id, 'b');
    
    $this->assert_throws('Exception', function() use($o) {
      $o->new_record = true;
    }, 'cannot set a protected property.');
    
    $this->assert_nothing_thrown(function() use($o) {
      $o->something = 'else';
    }, 'can set a public property');
    
    $this->assert_false($o->new_record);
    $this->assert_equal($o->something, 'else');
  }
}

?>
