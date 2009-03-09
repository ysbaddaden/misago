<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class FakeRecord extends ActiveRecord_Record { }

class Test_ActiveRecord_Record extends Unit_Test
{
  function test_new()
  {
    $record = new FakeRecord();
    
    $this->assert_equal("__set", $record->id = 123, 123);
    $this->assert_equal("__get", $record->id, 123);
    
    $this->assert_true("id is set", isset($record->id));
    $this->assert_false("title isn't set", isset($record->title));
    
    unset($record->id);
    $this->assert_false("id has been unset", isset($record->id));
    
    $record->ary = array();
    $this->assert_true("attribute set to empty array is set", isset($record->ary));
    $this->assert_true("attribute set to empty array is empty", empty($record->ary));
  }
  
  function test_iterates()
  {
    $record = new FakeRecord();
    $record->id = 123;
    $record->title = 'a';
    
    $data = array();
    foreach($record as $attribute => $value) {
      $data[$attribute] = $value;
    }
    
    $this->assert_equal("must iterate throught virtual attributes only",
      $data, array('id' => 123, 'title' => 'a'));
  }
}

new Test_ActiveRecord_Record();

?>
