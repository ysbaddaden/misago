<?php

$location = dirname(__FILE__).'/../../..';
$_SERVER['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class FakeRecord extends ActiveRecord_Record { }

# TODO: Test dirty objects.
# TODO: Test attributes().
class Test_ActiveRecord_Record extends Unit_TestCase
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
  
  function test_dirty_object()
  {
    $product = new Product();
    $this->assert_false('new & empty: model', $product->changed);
    $this->assert_false('new & empty: attribute', $product->name_changed);
    
    $product = new Product(array('name' => 'qwerty', 'price' => 50));
    $this->assert_false('new: model', $product->changed);
    $this->assert_false('new: attribute', $product->name_changed);
    
    $product->name = 'azerty';
    $this->assert_true("model has changed", $product->changed);
    $this->assert_true("attribute has changed", $product->name_changed);
    $this->assert_false("unchanged attribute", $product->price_changed);
    $this->assert_equal("original attribute value", $product->name_was, 'qwerty');
    $this->assert_equal("original+new attribute value", $product->name_change, array('qwerty', 'azerty'));
    
    $product->name = 'swerty';
    $this->assert_equal("other change: original is still the same", $product->name_was, 'qwerty');
    $this->assert_equal("other change: new has changed", $product->name_change, array('qwerty', 'swerty'));
    
    $product->save();
    $this->assert_false("saving resets state on model", $product->changed);
    $this->assert_false("saving resets state on attributes", $product->name_changed);
    
    $product->name = 'swerty';
    $this->assert_false("model didn't change (assigned same value)", $product->changed);
    $this->assert_false("attribute state didn't change", $product->name_changed);
    $this->assert_equal("attribute didn't change", $product->name_change, null);
    
    $product->name = 'azerty';
    $product->name = 'swerty';
    $this->assert_false("model previously changed, but came back to original", $product->changed);
    $this->assert_false("attribute state still hasn't change", $product->name_changed);
    
    $product->price = 16;
    $product->name  = 'swerty';
    $this->assert_true("assigning same value, but another attribute has changed", $product->changed);
  }
  
  function test_changes_and_changed()
  {
    $product = new Product(array('name' => 'aze', 'price' => 15));
    
    $product->name = 'rty';
    $this->assert_equal('', $product->changes(), array('name' => 'rty'));
    $this->assert_equal('', $product->changed(), array('name'));
    
    $product->price = 99;
    $this->assert_equal('', $product->changes(), array('name' => 'rty', 'price' => 99));
    $this->assert_equal('', $product->changed(), array('name', 'price'));
  }
}

new Test_ActiveRecord_Record();

?>
