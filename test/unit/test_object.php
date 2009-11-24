<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../test_app/config/boot.php";

class SomeOtherObject extends \Misago\Object
{
  private $object;
  
  function __construct($object)
  {
    $this->object = $object;
  }
  
  function module_method() {
    return $this->object->id();
  }
  
  static function module_static_method() {
    return 'f';
  }
}

class SomeObject extends \Misago\Object
{
  protected $new_record  = false;
  protected $table_name  = 'some_objects';
  protected $primary_key = 'private';
  
  private   $id = 'a';
  
  static function __constructStatic() {
    static::include_module('SomeOtherObject');
  }
  
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
SomeObject::__constructStatic();

class TestObject extends Misago\Unit\Test
{
  function test_attributes_as_methods()
  {
    $o = new SomeObject();
    
    $this->assert_false($o->new_record);
    $this->assert_equal($o->table_name, 'some_objects');
    $this->assert_null($o->primary_key);
    
    $this->assert_equal($o->id, 'a');
    $id = $o->id = 'b';
    $this->assert_equal($id, 'b', 'set value');
    $this->assert_equal($o->id, 'b', 'value has been set');
  }
  
  function test_include_module()
  {
    $o = new SomeObject();
    $this->assert_equal($o->module_method(), 'a');
    $this->assert_equal($o->module_method, 'a');
    
    $o->id = 12;
    $this->assert_equal($o->module_method(), 12);
    
    $this->assert_equal(SomeObject::module_static_method(), 'f');
  }
}

new TestObject();

?>
