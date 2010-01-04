<?php
require_once __DIR__.'/../../unit.php';

class FakeRecord extends Misago\ActiveRecord\Record
{
  static function columns()
  {
    return array(
      'id'    => array(),
      'title' => array()
    );
  }
}

class Test_ActiveRecord_Record extends Misago\Unit\TestCase
{
  function test_new()
  {
    $record = new FakeRecord();
    
    $this->assert_equal($record->id = 123, 123);
    $this->assert_equal($record->id, 123);
    
    $this->assert_true(isset($record->id));
    $this->assert_false(isset($record->title), "title isn't set yet");
    
    unset($record->id);
    $this->assert_false(isset($record->id));
    
    $record->ary = array();
    $this->assert_true(isset($record->ary), "attribute set as empty array is set");
    $this->assert_true(empty($record->ary), "attribute set as empty array is empty");
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
    
    $this->assert_equal($data, array('id' => 123, 'title' => 'a'), "must iterate throught virtual attributes only");
  }
  
  function test_dirty_object()
  {
    $product = new Product();
    $this->assert_false($product->changed);
    $this->assert_false($product->name_changed);
    
    $product = new Product(array('name' => 'qwerty', 'price' => 50));
    $this->assert_false($product->changed);
    $this->assert_false($product->name_changed);
    
    $product->name = 'azerty';
    $this->assert_true($product->changed, "model has changed");
    $this->assert_true($product->name_changed, "attribute has changed");
    $this->assert_false($product->price_changed, "unchanged attribute");
    $this->assert_equal($product->name_was, 'qwerty', "original attribute value");
    $this->assert_equal($product->name_change, array('azerty', 'qwerty'), "original+new attribute value");
    
    $product->name = 'swerty';
    $this->assert_equal($product->name_was, 'qwerty', "other change: original is still the same");
    $this->assert_equal($product->name_change, array('swerty', 'qwerty'), "other change: new has changed");
    
    $product->save();
    $this->assert_false($product->changed, "saving resets state on model");
    $this->assert_false($product->name_changed, "saving resets state on attributes");
    
    $product->name = 'swerty';
    $this->assert_false($product->changed, "model didn't change (assigned same value)");
    $this->assert_false($product->name_changed, "attribute state didn't change");
    $this->assert_equal($product->name_change, null, "attribute didn't change");
    
    $product->name = 'azerty';
    $product->name = 'swerty';
    $this->assert_false($product->changed, "model previously changed, but came back to original");
    $this->assert_false($product->name_changed, "attribute state still hasn't change");
    
    $product->price = 16;
    $product->name  = 'swerty';
    $this->assert_true($product->changed, "assigning same value, but another attribute has changed");
  }
  
  function test_changes_and_changed()
  {
    $product = new Product(array('name' => 'aze', 'price' => 15));
    
    $product->name = 'rty';
    $this->assert_equal($product->changes(), array('name' => 'rty'));
    $this->assert_equal($product->changed(), array('name'));
    
    $product->price = 99;
    $this->assert_equal($product->changes(), array('name' => 'rty', 'price' => 99));
    $this->assert_equal($product->changed(), array('name', 'price'));
  }
}

?>
