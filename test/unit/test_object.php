<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../test_app/config/boot.php";

class SomeObject extends Object
{
  protected $new_record  = false;
  protected $table_name  = 'some_objects';
  protected $primary_key = 'private';
  protected $attr_read   = array('new_record', 'table_name');
  
  private $id = 'a';
  
  function id($id=null)
  {
    if ($id !== null) {
      return $this->id = $id;
    }
    return $this->id;
  }
}

class TestObject extends Unit_Test
{
  function test_attr_read()
  {
    $o = new SomeObject();
    $this->assert_false($o->new_record);
    $this->assert_equal($o->table_name, 'some_objects');
    $this->assert_null($o->primary_key);
  }
  
  function test_functions_as_attributes()
  {
    $o = new SomeObject();
    $this->assert_equal($o->id, 'a');
    $this->assert_equal($o->id = 'b', 'b');
    $this->assert_equal($o->id, 'b');
  }
}

new TestObject();

?>
