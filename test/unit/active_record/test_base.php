<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../test/test_app/config/boot.php';

class Orphan extends ActiveRecord_Base {
  
}

class Test_ActiveRecord_Base extends Unit_TestCase
{
  function test_no_such_table()
  {
    try
    {
      new Orphan();
      $test = false;
    }
    catch(ActiveRecord_StatementInvalid $e) {
      $test = true;
    }
    $this->assert_true($test, "Must throw an ActiveRecord_StatementInvalid exception (no such table)");
  }
  
  function test_column_names()
  {
    $product = new Product();
    $column_names = $product->column_names(); sort($column_names);
    $this->assert_equal($column_names, array('created_at', 'description', 'id', 'in_stock', 'name', 'price', 'updated_at'));
    
    $basket = new Basket();
    $column_names = $basket->column_names(); sort($column_names);
    $this->assert_equal($column_names, array('created_at', 'id', 'order_id', 'product_id', 'updated_at'));
  }
  
  function test_new()
  {
    $product = new Product();
    $this->assert_equal(get_class($product), 'Product');
    $this->assert_instance_of($product, 'ActiveRecord_Base');
    $this->assert_instance_of($product, 'ActiveRecord_Record');
    
    $product = new Product(array('name' => 'azerty', 'price' => 18.99));
    $this->assert_equal($product->name, 'azerty');
    $this->assert_equal($product->price, 18.99);
  }
  
  function test_create()
  {
    $this->truncate('products');
    
    $product = new Product();
    $product = $product->create(array('name' => 'azerty', 'price' => 9.95));
    
    $this->assert_equal(get_class($product), 'Product');
    $this->assert_equal($product->name, 'azerty');
    $this->assert_equal($product->price, 9.95);
    $this->assert_type($product->id, 'integer');
  }
  
  function test_create_many()
  {
    $data1 = array('name' => "qwerty", 'price' =>  5.98);
    $data2 = array('name' => "bepo",   'price' => 10.55);
  
    $product = new Product();
    $products = $product->create($data1, $data2);
    
    $this->assert_equal(get_class($products[0]), 'Product');
    $this->assert_equal($products[1]->name, 'bepo');
    $this->assert_equal($products[0]->price, 5.98);
  }
  
  function test_find_all()
  {
    $this->fixtures('products');
    
    $product = new Product();
    
    $products = $product->find();
    $this->assert_equal(count($products), 3);
    $this->assert_instance_of($products[0], 'Product');
    
    $products_more = $product->find(':all');
    $this->assert_equal(count($products), 3);
    $this->assert_equal($products_more[0]->id, $products[0]->id);
  }
  
  function test_find_one()
  {
    $product = new Product();
    
    $options = array('conditions' => array('id' => 2));
    $product = $product->find(':first', $options);
    
    $this->assert_instance_of($product, 'Product');
    $this->assert_equal($product->name, 'qwerty');
    $this->assert_type($product->price, 'double');
    $this->assert_equal($product->price, 4.98);
  }
  
  function test_find_all_with_limit()
  {
    $product = new Product();
    
    $options  = array('limit' => 2);
    $products = $product->find(':all', $options);
    $this->assert_instance_of($products[0], 'Product');
    $this->assert_equal(count($products), 2);
    
    $options  = array('limit' => 2, 'page' => 2);
    $products = $product->find(':all', $options);
    $this->assert_equal(count($products), 1);
  }
  
  function test_find_with_order()
  {
    $product = new Product();
    
    $options = array('order' => 'name desc');
    $products = $product->find(':all', $options);
    $this->assert_equal($products[1]->name, 'bepo');
    
    $options = array('order' => 'products.name desc');
    $products = $product->find(':all', $options);
    $this->assert_equal($products[1]->name, 'bepo');
    
    $options = array('order' => 'name desc');
    $product = $product->find(':first', $options);
    $this->assert_equal($product->name, 'qwerty');
  }
  
  function test_with_all_options()
  {
    $product = new Product();
    
    $options = array('conditions' => 'id > 1', 'order' => 'name desc');
    $products = $product->find(':all', $options);
    $this->assert_equal(count($products), 2);
    
    $options = array('conditions' => 'id > 1', 'order' => 'id asc', 'limit' => 1);
    $products = $product->find(':all', $options);
    $this->assert_equal(count($products), 1);
    $this->assert_equal($products[0]->id, 2, "skipped first result");
  }
  
  function test_find_id()
  {
    $product = new Product();
    
    $product = $product->find(3);
    $this->assert_equal($product->id, 3);
    $this->assert_equal($product->name, 'azerty');
    
    $product = new Product(2);
    $this->assert_equal($product->id, 2);
    $this->assert_equal($product->name, 'qwerty');
  }
  
  function test_find_with_select()
  {
    $product = new Product();
    $product = $product->find(':first', array('select' => 'id'));
    
    $this->assert_true(isset($product->id));
    $this->assert_false(isset($product->name));
  }
  
  function test_find_shortcuts()
  {
    $product = new Product();
    
    $products = $product->all();
    $this->assert_equal(count($products), 3);
    
    $product = $product->first();
    $this->assert_instance_of($product, 'Product');
  }
  
  function test_find_by_sql()
  {
    $product = new Product();
    $products = $product->find_by_sql('select * from products');
    $this->assert_type($products, 'array');
    $this->assert_equal(count($products), 3);
    $this->assert_instance_of($products[0], 'Product');

    $products = $product->find_by_sql('select id from products where id = 2');
    $this->assert_equal(count($products), 1);
    $this->assert_equal($products[0]->id, 2);
  }
  
  function test_raw_find_by_sql()
  {
    $product = new Product();
    $ary = $product->raw_find_by_sql('select id,name from products order by id asc');
    
    $this->assert_type($ary, 'array');
    $this->assert_type($ary[0], 'array');
    $this->assert_equal($ary[0][0], '1');
  }
  
  function test_count_by_sql()
  {
    $product = new Product();
    $this->assert_equal($product->count_by_sql('select count(*) from products'), 3);
    $this->assert_equal($product->count_by_sql('select count(*) from products where id = 1'), 1);
  }
  
  function test_update()
  {
    $product = new Product();
    $product = $product->update(1, array('name' => 'swerty'));
    
    $this->assert_equal(get_class($product), 'Product');
    $this->assert_equal($product->name, 'swerty', "attribute has changed");
    
    $product = $product->update(1, array('name' => 'swerty2', 'some_virtual_field' => ''));
    $product = new Product(1);
    $this->assert_equal($product->name, 'swerty2');
  }
  
  # TEST: Test failures when updating many records.
  function test_update_many()
  {
    $product = new Product();
    $products = $product->update(array(1, 2), array(array('name' => 'swerty'), array('name' => 'bepo')));
    
    $this->assert_equal(get_class($products[0]), 'Product');
    $this->assert_equal($products[1]->name, 'bepo');
    $this->assert_equal($products[0]->name, 'swerty');
  }
  
  function test_update_all()
  {
    $product = new Product();
    
    $updates = array('updated_at' => '2008-12-21 00:01:00');
    $product->update_all($updates);
    $products = $product->all();
    $this->assert_equal(
      array((string)$products[0]->updated_at, (string)$products[1]->updated_at, (string)$products[2]->updated_at),
      array('2008-12-21 00:01:00', '2008-12-21 00:01:00', '2008-12-21 00:01:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:02:00');
    $product->update_all($updates, 'id = 1');
    $products = $product->all();
    $this->assert_equal(
      array((string)$products[0]->updated_at, (string)$products[1]->updated_at, (string)$products[2]->updated_at),
      array('2008-12-21 00:02:00', '2008-12-21 00:01:00', '2008-12-21 00:01:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:04:00');
    $product->update_all($updates, null, array('limit' => 2, 'order' => 'id desc'));
    $products = $product->all();
    $this->assert_equal(
      array((string)$products[0]->updated_at, (string)$products[1]->updated_at, (string)$products[2]->updated_at),
      array('2008-12-21 00:02:00', '2008-12-21 00:04:00', '2008-12-21 00:04:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:03:00');
    $product->update_all($updates, null, array('limit' => 1, 'order' => 'id asc'));
    $products = $product->all();
    $this->assert_equal(
      array((string)$products[0]->updated_at, (string)$products[1]->updated_at, (string)$products[2]->updated_at),
      array('2008-12-21 00:03:00', '2008-12-21 00:04:00', '2008-12-21 00:04:00')
    );
  }
  
  function test_new_record()
  {
    $product = new Product(array('name' => 'mwerty', 'price' => 6));
    $this->assert_true($product->new_record());
    
    $product = new Product(1);
    $this->assert_false($product->new_record());
    
    $products = $product->all();
    $this->assert_false($products[0]->new_record());
  }
  
  function test_save()
  {
    $this->truncate('products');
    
    # save: create
    $product = new Product(array('name' => 'mwerty', 'price' => 6));
    $this->assert_true($product->new_record(), "before creation: is a new record");
    $this->assert_true($product->save(), "creates record");
    $this->assert_false($product->new_record(), "after creation: no longer a new record");
    
    # save: update
    $product->name = 'pwerty';
    $this->assert_true($product->save(), 'updates record');
  }
  
  function test_delete()
  {
    $this->fixtures('products');
    
    $product = new Product(1);
    $product->delete();
    $this->assert_equal($product->find(1), null);
    
    $product->delete(2);
    $this->assert_equal($product->find(2), null);
  }
  
  function test_delete_all()
  {
    $this->fixtures('products');
    $product = new Product();
    
    $product->delete_all();
    $products = $product->all();
    $this->assert_equal(count($products), 0);
    
    $this->fixtures('products');
    
    $product->delete_all('id = 1');
    $product = $product->first(array('order' => 'id asc'));
    $this->assert_equal($product->name, 'qwerty', "delete_all with conditions");
    
    $this->fixtures('products');
    
    $product->delete_all(null, array('limit' => 1));
    $products = $product->all();
    $this->assert_equal(count($products), 2, "delete_all with limit");
    
    $this->fixtures('products');
    
    $product->delete_all(null, array('limit' => 2, 'order' => 'id desc'));
    $product = $product->first();
    $this->assert_equal($product->name, 'bepo', "delete_all with limit+order");
  }
  
  function test_destroy()
  {
    $this->fixtures('products');
    $product = new Product();
    
    $product = new Product(1);
    $product->destroy();
    $this->assert_equal($product->find(1), null);
    
    $product->destroy(2);
    $this->assert_equal($product->find(2), null);
  }
  
  function test_destroy_all()
  {
    $this->fixtures('products');
    $product = new Product();
    
    $product->destroy_all();
    $products = $product->all();
    $this->assert_equal(count($products), 0);
    
    $this->fixtures('products');
    
    $product->destroy_all('id = 1');
    $product = $product->first(array('order' => 'id asc'));
    $this->assert_equal($product->name, 'qwerty', 'with conditions');
    
    $this->fixtures('products');
    
    $product->destroy_all(null, array('limit' => 1));
    $products = $product->all();
    $this->assert_equal(count($products), 2, 'with limit');
    
    $this->fixtures('products');
    
    $product->destroy_all(null, array('limit' => 2, 'order' => 'id desc'));
    $product = $product->first();
    $this->assert_equal($product->name, 'bepo', "with limit+order");
  }
  
  function test_update_attributes()
  {
    $this->truncate('products');
    
    $product = new Product();
    $product = $product->create(array('id' => 1, 'name' => 'bepo', 'price' => 9.99));
    
    $product->update_attributes(array('price' => 10.95, 'name' => 'Bepo'));
    $this->assert_equal(array($product->name, $product->price), array('Bepo', 10.95), "object must have been updated");
    
    $product = new Product(1);
    $this->assert_equal(array($product->name, $product->price), array('Bepo', 10.95), "changes must have been recorded");
    
    $product->update_attributes(array('in_stock' => null));
    $this->assert_null($product->in_stock, "set a field to null");
    
    $product = new Product(1);
    $this->assert_null($product->in_stock, "null field must have been recorded");
  }
  
  function test_update_attribute()
  {
    $product = new Product(1);
    $product->update_attribute('in_stock', true);
    $this->assert_equal($product->in_stock, true);
    
    $product = new Product(1);
    $this->assert_equal($product->in_stock, true);
    
    $product->update_attribute('in_stock', null);
    $this->assert_null($product->in_stock);
    
    $product = new Product(1);
    $this->assert_null($product->in_stock, "setting a field null must have been recorded");
  }
	
  function test_find_with_joins()
  {
    $this->fixtures("products, orders, baskets, invoices");
  	
    $product = new product();
    $products = $product->find(':all', array(
      'select'     => 'products.id',
      'conditions' => 'baskets.order_id = 1',
      'joins'      => 'INNER JOIN baskets ON baskets.product_id = products.id',
    ));
    $this->assert_equal(count($products), 3);
    $this->assert_equal(array($products[0]->id, $products[1]->id, $products[2]->id), array(1, 2, 3));
    
    $products = $product->find(':all', array(
      'select'     => 'products.id',
      'conditions' => array('orders.id' => 1),
      'joins'      => array(
        'INNER JOIN baskets ON baskets.product_id = products.id',
        'INNER JOIN orders  ON orders.id = baskets.order_id'
      ),
    ));
    $this->assert_equal(count($products), 3);
    $this->assert_equal(array($products[0]->id, $products[1]->id, $products[2]->id), array(1, 2, 3));
    
    $basket = new Basket();
    $baskets = $basket->find(':values', array(
      'select'     => 'baskets.id',
      'joins'      => 'order',
      'conditions' => 'orders.id = 1',
    ));
    $this->assert_equal(count($baskets), 3);
    $this->assert_equal(array($baskets[0][0], $baskets[1][0], $baskets[2][0]), array(1, 2, 3));
  }
  
  function test_exists()
  {
    $product = new Product();
    $this->assert_true($product->exists(1));
    
    $product = new Product();
    $this->assert_false($product->exists(512));
  }
  
  function test_magic_find_methods()
  {
    $product  = new Product();
    
    $products = $product->find_all_by_name('azerty');
    $this->assert_equal(count($products), 1);
    $this->assert_equal($products[0]->id, 3);
    
    $product = $product->find_by_id(1);
    $this->assert_equal($product->name, 'bepo');
    
    $products = $product->find_all();
    $this->assert_equal(count($products), 3);
    
    $products = $product->find_first();
    $this->assert_equal(count($products), 1);
    
    $products = $product->find_all(array('limit' => 1, 'page' => 2, 'order' => 'id asc'));
    $this->assert_equal(count($products), 1);
    $this->assert_equal($products[0]->id, 2);
  }
  
  function test_find_values()
  {
    $product = new Product();
    $options = $product->find(':values', array('select' => 'name, id', 'order' => 'name asc'));
    
    $this->assert_equal(count($options), 3);
    $this->assert_equal($options[0][0], 'azerty');
    $this->assert_equal($options[0][1], '3');
    $this->assert_equal($options[1][0], 'bepo');
    $this->assert_equal($options[1][1], '1');

    $options = $product->values(array('select' => 'name, id', 'order' => 'name asc'));
    $this->assert_equal(count($options), 3);
  }
  
  function test_validations()
  {
    $product = new Product(1);
    $this->assert_true($product->save());
    $this->assert_true($product->errors->is_empty());
    
    $product = $product->update(1, array('name' => ''));
    $this->assert_false($product->errors->is_empty(), "must fail on update");
    $this->assert_true($product->errors->is_invalid('name'), "name is invalid");
    
    $product = $product->create(array());
    $this->assert_false($product->errors->is_empty(), "must fail on create");
    $this->assert_true($product->errors->is_invalid('name'), "name is invalid");
  }
  
  function test_merge_options()
  {
    $programmer = new Programmer();
    
    $a = array();
    $b = array();
    $c = $programmer->merge_options($a, $b);
    $this->assert_equal($c, array());
    
    $a = array('limit' => 10, 'select' => 'a.*');
    $b = array();
    $c = $programmer->merge_options($a, $b);
    $this->assert_equal($c, array('limit' => 10, 'select' => 'a.*'));
    
    $a = array();
    $b = array('conditions' => 'a = 1', 'limit' => 100);
    $c = $programmer->merge_options($a, $b);
    $this->assert_equal($c, array('conditions' => 'a = 1', 'limit' => 100));
    
    $a = array('conditions' => "b <> 'aze'");
    $b = array('conditions' => 'a = 1', 'limit' => 100);
    $c = $programmer->merge_options($a, $b);
    $this->assert_equal($c, array('conditions' => "(b <> 'aze') AND (a = 1)", 'limit' => 100));
    
    $a = array('conditions' => "b <> 'aze'");
    $b = array('conditions' => array('a = :a', array('a' => 12)), 'limit' => 100);
    $c = $programmer->merge_options($a, $b);
    $this->assert_equal($c, array('conditions' => "(b <> 'aze') AND (a = '12')", 'limit' => 100));
    
    $a = array('order' => 'created_at asc');
    $b = array('order' => 'created_at desc');
    $c = $programmer->merge_options($a, $b);
    $this->assert_equal($c, array('order' => 'created_at desc'));
  }
  
  function test_find_with_default_scope()
  {
    $invoice = new Invoice();
    $invoices = $invoice->find(':all');
    
    # result is ordered (because of default_scope)
    $this->assert_equal($invoices[0]->id, 2);
    $this->assert_equal($invoices[1]->id, 1);
  }
  
  function test_eager_loading_with_default_scope()
  {
    $this->fixtures('baskets,orders');
    
    $order  = new Order();
    $orders = $order->find(':all', array('conditions' => 'id = 1', 'include' => 'baskets'));
    
    # result is ordered (because of default_scope)
    $this->assert_equal($orders[0]->baskets[0]->id, 2);
    $this->assert_equal($orders[0]->baskets[1]->id, 1);
    $this->assert_equal($orders[0]->baskets[2]->id, 3);
  }
  
  function test_human_name()
  {
    $order = new Order();
    $this->assert_equal($order->human_name(), 'Order', 'defaults to String::humanize()');
    
    $monitoring = new Monitoring();
    $this->assert_equal($monitoring->human_name(), 'Guardian', 'specified human name (I18n)');
  }
  
  function test_human_attribute_name()
  {
    $monitoring = new Monitoring();
    $this->assert_equal($monitoring->human_attribute_name('title'), 'Title', 'defaults to humanize()');
    $this->assert_equal($monitoring->human_attribute_name('length_string'), 'Length string');
    $this->assert_equal($monitoring->human_attribute_name('length_string2'), 'My length', 'specified translation (I18n)');
  }
}

new Test_ActiveRecord_Base();

?>
