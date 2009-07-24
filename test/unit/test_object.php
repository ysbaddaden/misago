<?php

$_SERVER['MISAGO_ENV'] = 'test';
require_once __DIR__."/../test_app/config/boot.php";

class SomeObject extends Object
{
  protected $new_record  = false;
  protected $table_name  = 'some_objects';
  protected $primary_key = 'private';
  protected $attr_read   = array('new_record', 'table_name');
}

class TestObject extends Unit_Test
{
  function test_attr_read()
  {
    $o = new SomeObject();
    $this->assert_false("", $o->new_record);
    $this->assert_equal("", $o->table_name, 'some_objects');
    $this->assert_null("",  $o->primary_key);
  }
}

new TestObject();

?>
