<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

# TODO: Test usage of virtual attributes.
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
  
  function test_with_all_options()
  {
    $product = new Product();
    
    $options = array('conditions' => 'id > 1', 'order' => 'name desc');
    $products = $product->find(':all', $options);
    $this->assert_equal("results count", count($products), 2);
    
    $options = array('conditions' => 'id > 1', 'order' => 'id asc', 'limit' => 1);
    $products = $product->find(':all', $options);
    $this->assert_equal("only one result", count($products), 1);
    $this->assert_equal("must have skip first", $products[0]->id, 2);
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
    
    $product = $product->update(1, array('name' => 'swerty2', 'some_virtual_field' => ''));
    $product = new Product(1);
    $this->assert_equal("virtual field", $product->name, 'swerty2');
  }
  
  # FIXME: Test failures when updating many records.
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
      array(new Time('2008-12-21 00:01:00'), new Time('2008-12-21 00:01:00'), new Time('2008-12-21 00:01:00'))
    );
    
    $updates = array('updated_at' => '2008-12-21 00:02:00');
    $product->update_all($updates, 'id = 1');
    $products = $product->all();
    $this->assert_equal("update_all with conditions",
      array($products[0]->updated_at, $products[1]->updated_at, $products[2]->updated_at),
      array(new Time('2008-12-21 00:02:00'), new Time('2008-12-21 00:01:00'), new Time('2008-12-21 00:01:00'))
    );
    
    $updates = array('updated_at' => '2008-12-21 00:03:00');
    $product->update_all($updates, null, array('limit' => 2));
    $products = $product->all();
    $this->assert_equal("update_all with limit",
      array($products[0]->updated_at, $products[1]->updated_at, $products[2]->updated_at),
      array(new Time('2008-12-21 00:03:00'), new Time('2008-12-21 00:03:00'), new Time('2008-12-21 00:01:00'))
    );
    
    $updates = array('updated_at' => '2008-12-21 00:04:00');
    $product->update_all($updates, null, array('limit' => 2, 'order' => 'id desc'));
    $products = $product->all();
    $this->assert_equal("update_all with limit+order",
      array($products[0]->updated_at, $products[1]->updated_at, $products[2]->updated_at),
      array(new Time('2008-12-21 00:03:00'), new Time('2008-12-21 00:04:00'), new Time('2008-12-21 00:04:00'))
    );
  }
  
  function test_new_record()
  {
    $product = new Product(array('name' => 'mwerty', 'price' => 6));
    $this->assert_true("new Product(attributes)", $product->new_record());
    
    $product = new Product(1);
    $this->assert_false("new Product(id)", $product->new_record());
    
    $products = $product->all();
    $this->assert_false("new Product(id)", $products[0]->new_record());
  }
  
  function test_save()
  {
    $db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    $db->execute('TRUNCATE products ;');
    
    # save: create
    $product = new Product(array('name' => 'mwerty', 'price' => 6));
    $this->assert_true("before creation: is a new record", $product->new_record());
    $this->assert_true("creates record", $product->save());
    $this->assert_false("after creation: isn't a new record", $product->new_record());
    
    # save: update
    $product->name = 'pwerty';
    $this->assert_true("updates record", $product->save());
  }
  
  function test_delete()
  {
    $product = new Product(1);
    $product->delete();
    $this->assert_equal("", $product->find(1), null);
    
    $product->delete(2);
    $this->assert_equal("", $product->find(2), null);
  }
  
  function test_delete_all()
  {
    $db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    $db->execute('TRUNCATE products ;');
    
    $data1   = array('name' => "qwerty", 'price' =>  5.98);
    $data2   = array('name' => "bepo",   'price' => 10.55);
    $product = new Product();
    
    $product->delete_all();
    $products = $product->all();
    $this->assert_equal("delete_all", count($products), 0);
    
    $product->create($data1, $data2);
    
    $product->delete_all('id = 1');
    $product = $product->first();
    $this->assert_equal("delete_all with conditions", $product->name, 'bepo');
    
    $product->delete_all();
    $product->create($data1, $data2);
    
    $product->delete_all(null, array('limit' => 1));
    
    $products = $product->all();
    $this->assert_equal("delete_all with limit", count($products), 1);
    
    $product->delete_all();
    $product->create($data1, $data2);
    
    $product->delete_all(null, array('limit' => 1, 'order' => 'id desc'));
    $product = $product->first();
    $this->assert_equal("delete_all with limit+order", count($products), 1);
    $this->assert_equal("delete_all with limit+order", $product->name, 'qwerty');
  }
  
  function test_destroy()
  {
    $db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    $db->execute('TRUNCATE products ;');
    
    $data1   = array('name' => "qwerty", 'price' =>  5.98);
    $data2   = array('name' => "bepo",   'price' => 10.55);
    $product = new Product();
    $product->create($data1, $data2);
    
    $product = new Product(1);
    $product->destroy();
    $this->assert_equal("", $product->find(1), null);
    
    $product->destroy(2);
    $this->assert_equal("", $product->find(2), null);
  }
  
  function test_destroy_all()
  {
    $db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    $db->execute('TRUNCATE products ;');
    
    $data1   = array('name' => "qwerty", 'price' =>  5.98);
    $data2   = array('name' => "bepo",   'price' => 10.55);
    $product = new Product();
    
    $product->destroy_all();
    $products = $product->all();
    $this->assert_equal("destroy_all", count($products), 0);
    
    $product->create($data1, $data2);
    
    $product->destroy_all('id = 1');
    $product = $product->first();
    $this->assert_equal("destroy_all with conditions", $product->name, 'bepo');
    
    $product->destroy_all();
    $product->create($data1, $data2);
    
    $product->destroy_all(null, array('limit' => 1));
    
    $products = $product->all();
    $this->assert_equal("destroy_all with limit", count($products), 1);
    
    $product->destroy_all();
    $product->create($data1, $data2);
    
    $product->destroy_all(null, array('limit' => 1, 'order' => 'id desc'));
    $product = $product->first();
    $this->assert_equal("destroy_all with limit+order", count($products), 1);
    $this->assert_equal("destroy_all with limit+order", $product->name, 'qwerty');
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
    
    $product->update_attributes(array('in_stock' => null));
    $this->assert_type("set a field to null", $product->in_stock, 'NULL');
    
    $product = new Product(1);
    $this->assert_type("null field must have been recorded", $product->in_stock, 'NULL');
    
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
    $product->update_attribute('in_stock', true);
    $this->assert_equal("basic update", $product->in_stock, true);
    
    $product = new Product(1);
    $this->assert_equal("basic update (recorded?)", $product->in_stock, true);
    
    $product->update_attribute('in_stock', null);
    $this->assert_type("set a field to null", $product->in_stock, 'NULL');
    
    $product = new Product(1);
    $this->assert_type("set a field to null (recorded?)", $product->in_stock, 'NULL');
  }
	
  function test_find_with_joins()
  {
  	$this->truncate('products');
    $this->fixtures("products, orders, baskets, invoices");
  	
  	
    $product = new product();
    $products = $product->find(':all', array(
      'select'     => 'products.id',
      'conditions' => 'baskets.order_id = 1',
      'joins'      => 'INNER JOIN baskets ON baskets.product_id = products.id',
    ));
    $this->assert_equal('simple join', count($products), 3);
    $this->assert_equal('simple join', array($products[0]->id, $products[1]->id, $products[2]->id), array(1, 2, 3));
    
    $products = $product->find(':all', array(
      'select'     => 'products.id',
      'conditions' => array('orders.id' => 1),
      'joins'      => array(
        'INNER JOIN baskets ON baskets.product_id = products.id',
        'INNER JOIN orders  ON orders.id = baskets.order_id'
      ),
    ));
    $this->assert_equal('multiple joins', count($products), 3);
    $this->assert_equal('multiple joins', array($products[0]->id, $products[1]->id, $products[2]->id), array(1, 2, 3));
  }
  
  function test_exists()
  {
    $product = new Product();
    $this->assert_true('', $product->exists(1));
    
    $product = new Product();
    $this->assert_false('', $product->exists(512));
  }
  
  function test_magic_find_methods()
  {
    $product  = new Product();
    
    $products = $product->find_all_by_name('azerty');
    $this->assert_equal('', count($products), 1);
    $this->assert_equal('', $products[0]->id, 3);
    
    $product = $product->find_by_id(1);
    $this->assert_equal('', $product->name, 'bepo');
    
    $products = $product->find_all();
    $this->assert_equal('', count($products), 3);
    
    $products = $product->find_first();
    $this->assert_equal('', count($products), 1);
    
    $products = $product->find_all(array('limit' => 1, 'page' => 2, 'order' => 'id asc'));
    $this->assert_equal('', count($products), 1);
    $this->assert_equal('', $products[0]->id, 2);
  }
  
  function test_find_values()
  {
    $product = new Product();
    $options = $product->find(':values', array('select' => 'name, id', 'order' => 'name asc'));
    
    $this->assert_equal('', count($options), 3);
    $this->assert_equal('', $options[0][0], 'azerty');
    $this->assert_equal('', $options[0][1], '3');
    $this->assert_equal('', $options[1][0], 'bepo');
    $this->assert_equal('', $options[1][1], '1');

    $options = $product->values(array('select' => 'name, id', 'order' => 'name asc'));
    $this->assert_equal('', count($options), 3);
  }
  
  function test_validations()
  {
    $product = new Product(1);
    $this->assert_true("", $product->save());
    $this->assert_true("", $product->errors->is_empty());
    
    $product = $product->update(1, array('name' => ''));
    $this->assert_false("must fail on update", $product->errors->is_empty());
    $this->assert_true("name is invalid", $product->errors->is_invalid('name'));
    
    $product = $product->create(array());
    $this->assert_false("must fail on create", $product->errors->is_empty());
    $this->assert_true("name is invalid", $product->errors->is_invalid('name'));
  }
  
  function test_merge_options()
  {
    $programmer = new Programmer();
    
    $a = array();
    $b = array();
    $c = $programmer->test_merge_options($a, $b);
    $this->assert_equal("", $c, array());
    
    $a = array('limit' => 10, 'select' => 'a.*');
    $b = array();
    $c = $programmer->test_merge_options($a, $b);
    $this->assert_equal("", $c, array('limit' => 10, 'select' => 'a.*'));
    
    $a = array();
    $b = array('conditions' => 'a = 1', 'limit' => 100);
    $c = $programmer->test_merge_options($a, $b);
    $this->assert_equal("", $c, array('conditions' => 'a = 1', 'limit' => 100));
    
    $a = array('conditions' => "b <> 'aze'");
    $b = array('conditions' => 'a = 1', 'limit' => 100);
    $c = $programmer->test_merge_options($a, $b);
    $this->assert_equal("", $c, array('conditions' => "(b <> 'aze') AND (a = 1)", 'limit' => 100));
    
    $a = array('conditions' => "b <> 'aze'");
    $b = array('conditions' => array('a = :a', array('a' => 12)), 'limit' => 100);
    $c = $programmer->test_merge_options($a, $b);
    $this->assert_equal("", $c, array('conditions' => "(b <> 'aze') AND (a = '12')", 'limit' => 100));
  }
  
  function test_find_with_default_scope()
  {
    $invoice = new Invoice();
    $invoices = $invoice->find(':all');
    
    $this->assert_equal('result must be ordered (a)', $invoices[0]->id, 2);
    $this->assert_equal('result must be ordered (b)', $invoices[1]->id, 1);
  }
  
  function test_eager_loading_with_default_scope()
  {
    $this->truncate('baskets,orders');
    $this->fixtures('baskets,orders');
    
    $order  = new Order();
    $orders = $order->find(':all', array('conditions' => 'id = 1', 'include' => 'baskets'));
    
    $this->assert_equal('result must be ordered (a)', $orders[0]->baskets[0]->id, 2);
    $this->assert_equal('result must be ordered (b)', $orders[0]->baskets[1]->id, 1);
    $this->assert_equal('result must be ordered (c)', $orders[0]->baskets[2]->id, 3);
  }
  
  function test_human_name()
  {
    $order = new Order();
    $this->assert_equal('defaults to String::humanize()', $order->human_name(), 'Order');
    
    $monitoring = new Monitoring();
    $this->assert_equal('specified human name (I18n)', $monitoring->human_name(), 'Guardian');
  }
  
  function test_human_attribute_name()
  {
    $monitoring = new Monitoring();
    $this->assert_equal('defaults to humanize()', $monitoring->human_attribute_name('title'), 'Title');
    $this->assert_equal('defaults to humanize()', $monitoring->human_attribute_name('length_string'), 'Length string');
    $this->assert_equal('specified translation',  $monitoring->human_attribute_name('length_string2'), 'My length');
  }
}

new Test_ActiveRecord_Base();

?>
