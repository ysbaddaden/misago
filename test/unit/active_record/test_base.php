<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/lib/unit_test.php";
require_once "$location/test/test_app/config/boot.php";

# cleanup
`cd $location/test/test_app; MISAGO_ENV=test script/db/drop`;
`cd $location/test/test_app; MISAGO_ENV=test script/db/create`;
`cd $location/test/test_app; MISAGO_ENV=test script/db/migrate`;

# fake objects
class Post extends ActiveRecord_Base {}

class Product extends ActiveRecord_Base
{
  
}

class Test_ActiveRecord_Base extends Unit_Test
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
    $this->assert_equal("must return 2 products only", count($products), 2);
    
    $options  = array('limit' => 2, 'page' => 2);
    $products = $product->find(':all', $options);
    $this->assert_equal("must return 1 product only", count($products), 1);
  }
  
  function test_find_with_order()
  {
    $product = new Product();
    $options = array('order' => 'name desc');
    $products = $product->find(':all', $options);
    $this->assert_equal("", $products[1]->name, 'bepo');
  }
  
  /*
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
  */
  
}

new Test_ActiveRecord_Base();

?>
