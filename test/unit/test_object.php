<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../test_app/config/boot.php";

class SomeOtherObject extends \Misago\Object
{
  function __construct()
  {
    
  }
  
  function module_method()
  {
    return 'e';
  }
}

class SomeObject extends \Misago\Object
{
  protected $include_modules = array('SomeOtherObject');
  protected $new_record      = false;
  protected $table_name      = 'some_objects';
  protected $primary_key     = 'private';
  
  private   $id = 'a';
  
  function id()
  {
    return $this->id;
  }
  
  function id_set($id)
  {
    return $this->id = $id;
  }
  
  function new_record()
  {
    return $this->new_record;
  }
  
  function table_name()
  {
    return $this->table_name;
  }
}

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
  
  function test_map_method()
  {
    $o = new SomeObject();
    $o->map_method($this, 'method_to_map');
    $o->map_method($this, 'method_to_map', 'meth');
    
    $this->assert_equal($o->method_to_map(), 'c');
    $this->assert_equal($o->meth(), 'c');
    $this->assert_equal($o->meth('d'), 'd');
  }
  
  function method_to_map($value=null)
  {
    return ($value === null) ? 'c' : $value;
  }
  
  function test_include_module()
  {
    $o = new SomeObject();
    $this->assert_equal($o->module_method(), 'e');
    $this->assert_equal($o->module_method, 'e');
  }
}

new TestObject();

?>
