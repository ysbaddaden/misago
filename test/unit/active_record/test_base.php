<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Base extends Unit_TestCase
{
  function test_no_such_table()
  {
    try
    {
      new Post();
      $test = false;
    }
    catch(ActiveRecord_StatementInvalid $e) {
      $test = true;
    }
    $this->assert_true("Must throw an ActiveRecord_StatementInvalid exception (no such table)", $test);
  }
  
  function test_new()
  {
    $product = new Product();
    $this->assert_equal('Product must be an instance of Product', get_class($product), 'Product');
    $this->assert_instance_of('Product must be an instance of ActiveRecord_Base',   $product, 'ActiveRecord_Base');
    $this->assert_instance_of('Product must be an instance of ActiveRecord_Record', $product, 'ActiveRecord_Record');
    
    $product = new Product(array('name' => 'azerty', 'price' => 18.99));
    $this->assert_equal("", $product->name, 'azerty');
    $this->assert_equal("", $product->price, 18.99);
  }
  
  function test_create()
  {
    $product = new Product();
    $product = $product->create(array('name' => 'azerty', 'price' => 9.95));
    
    $this->assert_equal('Created product must be a Product', get_class($product), 'Product');
    $this->assert_equal("", $product->name, 'azerty');
    $this->assert_equal("", $product->price, 9.95);
    $this->assert_type("product.id must be an integer", $product->id, 'integer');
  }
  
  function test_create_many()
  {
    $data1 = array('name' => "qwerty", 'price' =>  5.98);
    $data2 = array('name' => "bepo",   'price' => 10.55);
  
    $product = new Product();
    $products = $product->create($data1, $data2);
    
    $this->assert_equal('Created product must be a Product', get_class($products[0]), 'Product');
    $this->assert_equal("", $products[1]->name, 'bepo');
    $this->assert_equal("", $products[0]->price, 5.98);
  }
  
  function test_find_all()
  {
    $product = new Product();
    
    $products = $product->find();
    $this->assert_equal("must find 3 products", count($products), 3);
    $this->assert_instance_of("array of products", $products[0], 'Product');
    
    $products_more = $product->find(':all');
    $this->assert_equal("must find 3 products", count($products), 3);
    $this->assert_equal("result must be equivalent to previous one", $products_more[0]->id, $products[0]->id);
  }
  
  function test_find_one()
  {
    $product = new Product();
    
    $options = array('conditions' => array('id' => 2));
    $product = $product->find(':first', $options);
    
    $this->assert_instance_of("instance of product", $product, 'Product');
    $this->assert_equal("product's name", $product->name, 'qwerty');
    $this->assert_type("product's price must be a float", $product->price, 'double');
    $this->assert_equal("product's price", $product->price, 5.98);
  }
  
  function test_find_all_with_limit()
  {
    $product = new Product();
    
    $options  = array('limit' => 2);
    $products = $product->find(':all', $options);
    $this->assert_instance_of("instances of Product", $products[0], 'Product');
    $this->assert_equal("limit only (must return 2 products only)", count($products), 2);
    
    $options  = array('limit' => 2, 'page' => 2);
    $products = $product->find(':all', $options);
    $this->assert_equal("limit+page (must return 1 product only)", count($products), 1);
  }
  
  function test_find_with_order()
  {
    $product = new Product();
    
    $options = array('order' => 'name desc');
    $products = $product->find(':all', $options);
    $this->assert_equal("", $products[1]->name, 'bepo');
    
    $options = array('order' => 'products.name desc');
    $products = $product->find(':all', $options);
    $this->assert_equal("", $products[1]->name, 'bepo');
    
    $options = array('order' => 'name desc');
    $product = $product->find(':first', $options);
    $this->assert_equal("", $product->name, 'qwerty');
  }
  
  function test_find_id()
  {
    $product = new Product();
    
    $product = $product->find(3);
    $this->assert_equal("", $product->id, 3);
    $this->assert_equal("", $product->name, 'bepo');
    
    $product = new Product(2);
    $this->assert_equal("", $product->id, 2);
    $this->assert_equal("", $product->name, 'qwerty');
  }
  
  function test_find_with_select()
  {
    $product = new Product();
    $product = $product->find(':first', array('select' => 'id'));
    
    $this->assert_true("", isset($product->id));
    $this->assert_false("", isset($product->name));
  }
  
  function test_find_shortcuts()
  {
    $product = new Product();
    
    $products = $product->all();
    $this->assert_equal("Product::all()", count($products), 3);
    
    $product = $product->first();

    $this->assert_instance_of("Product::first()", $product, 'Product');
  }
  
  function test_update()
  {
    $product = new Product();
    $product = $product->update(1, array('name' => 'swerty'));
    
    $this->assert_equal('must be a Product', get_class($product), 'Product');
    $this->assert_equal("attribute must have changed", $product->name, 'swerty');
  }
  
  function test_update_many()
  {
    $product = new Product();
    $products = $product->update(array(1, 2), array(array('name' => 'swerty'), array('name' => 'bepo')));
    
    $this->assert_equal('must be an array of products', get_class($products[0]), 'Product');
    $this->assert_equal("", $products[1]->name, 'bepo');
    $this->assert_equal("", $products[0]->name, 'swerty');
  }
  
  function test_update_all()
  {
    $product = new Product();
    
    $updates = array('updated_at' => '2008-12-21 00:01:00');
    $product->update_all($updates);
    $products = $product->all();
    $this->assert_equal("update_all",
      array($products[0]->updated_at, $products[1]->updated_at, $products[2]->updated_at),
      array('2008-12-21 00:01:00', '2008-12-21 00:01:00', '2008-12-21 00:01:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:02:00');
    $product->update_all($updates, 'id = 1');
    $products = $product->all();
    $this->assert_equal("update_all with conditions",
      array($products[0]->updated_at, $products[1]->updated_at, $products[2]->updated_at),
      array('2008-12-21 00:02:00', '2008-12-21 00:01:00', '2008-12-21 00:01:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:03:00');
    $product->update_all($updates, null, array('limit' => 2));
    $products = $product->all();
    $this->assert_equal("update_all with limit",
      array($products[0]->updated_at, $products[1]->updated_at, $products[2]->updated_at),
      array('2008-12-21 00:03:00', '2008-12-21 00:03:00', '2008-12-21 00:01:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:04:00');
    $product->update_all($updates, null, array('limit' => 2, 'order' => 'id desc'));
    $products = $product->all();
    $this->assert_equal("update_all with limit+order",
      array($products[0]->updated_at, $products[1]->updated_at, $products[2]->updated_at),
      array('2008-12-21 00:03:00', '2008-12-21 00:04:00', '2008-12-21 00:04:00')
    );
  }
  
  /*
  function test_update_attributes()
  {
    $product = new Product(1);
    $product->update_attribute('name', 'poiuyt');
    $this->assert_equal('name must have changed', $product->name, 'poiuyt');
    
    $product = new Product(1);
    $this->assert_equal('change must have been recorded', $product->name, 'poiuyt');
  }
  */
  
  function test_delete_all()
  {
    $data1   = array('name' => "qwerty", 'price' =>  5.98);
    $data2   = array('name' => "bepo",   'price' => 10.55);
    $product = new Product();
    
    
    $product->delete_all();
    $products = $product->all();
    $this->assert_equal("delete_all", count($products), 0);
    
    
    $product->create($data1, $data2);
    
    $product->delete_all('id = 4');
    $product = $product->first();
    $this->assert_equal("delete_all with conditions", $product->name, 'bepo');
    
    
    $product->delete_all();
    $product->create($data1, $data2);
    
    $product->delete_all(null, array('limit' => 1));
    $products = $product->all();
    
    $this->assert_equal("delete_all with limit", count($products), 1);
    $this->assert_equal("delete_all with limit", $products[0]->name, 'bepo');
    
    
    $product->delete_all();
    $product->create($data1, $data2);
    
    $product->delete_all(null, array('limit' => 1, 'order' => 'id desc'));
    $product = $product->first();
    $this->assert_equal("delete_all with limit+order", count($products), 1);
    $this->assert_equal("delete_all with limit+order", $product->name, 'qwerty');
  }
  
  function test_update_attributes()
  {
    $product = new Product();
    $product->delete_all();
    $product = $product->create(array('id' => 1, 'name' => 'bepo', 'price' => 9.99));
    
    $product->update_attributes(array('price' => 10.95, 'name' => 'Bepo'));
    $this->assert_equal("object must have been updated", array($product->name, $product->price), array('Bepo', 10.95));
    
    $product = new Product(1);
    $this->assert_equal("changes must have been recorded", array($product->name, $product->price), array('Bepo', 10.95));
    
    $product->update_attributes(array('created_at' => null));
    $this->assert_type("set a field to null", $product->updated_at, 'NULL');
    
    $product = new Product(1);
    $this->assert_type("change must have been recorded", $product->updated_at, 'NULL');
    
    $product->price = 10.99;
    $product->name  = 'bepo';
    $product->update_attributes('name, price');
    $product = new Product(1);
    $this->assert_equal("update a list of fields (as string)", array($product->name, $product->price), array('bepo', 10.99));
    
    $product->price = 8.95;
    $product->name  = 'Bepo';
    $product->update_attributes(array('name', 'price'));
    $product = new Product(1);
    $this->assert_equal("update a list of fields (as array)", array($product->name, $product->price), array('Bepo', 8.95));
  }
  
  function test_update_attribute()
  {
    $product = new Product(1);
    $product->update_attribute('updated_at', '2008-12-31 00:00:01');
    $this->assert_equal("basic update", $product->updated_at, '2008-12-31 00:00:01');
    
    $product = new Product(1);
    $this->assert_equal("basic update (recorded?)", $product->updated_at, '2008-12-31 00:00:01');
    
    $product->update_attribute('updated_at', null);
    $this->assert_type("set a field to null", $product->updated_at, 'NULL');
    
    $product = new Product(1);
    $this->assert_equal("set a field to null (recorded?)", $product->updated_at, null);
    $this->assert_type("set a field to null (recorded?)", $product->updated_at, 'NULL');
  }
}

new Test_ActiveRecord_Base();

?>
