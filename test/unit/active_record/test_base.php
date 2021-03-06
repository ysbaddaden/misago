<?php
require_once __DIR__.'/../../unit.php';
require_once 'Misago/ActiveRecord/Exception.php';
use Misago\ActiveRecord;

class Orphan extends ActiveRecord\Base {}

class Test_ActiveRecord_Base extends Misago\Unit\TestCase
{
  protected $fixtures = array();
  
  function test_no_such_table()
  {
    $this->assert_throws('Misago\ActiveRecord\StatementInvalid', function() {
      Orphan::columns();
    }, "Must throw an ActiveRecord\StatementInvalid exception (no such table)");
  }
  
  function test_column_names()
  {
    $column_names = Product::column_names(); sort($column_names);
    $this->assert_equal($column_names, array('created_at', 'description', 'id', 'in_stock', 'name', 'price', 'updated_at'));
    
    $column_names = Basket::column_names(); sort($column_names);
    $this->assert_equal($column_names, array('created_at', 'id', 'order_id', 'product_id', 'updated_at'));
  }
  
  function test_new()
  {
    $product = new Product();
    $this->assert_equal(get_class($product), 'Product');
    $this->assert_instance_of($product, 'Misago\ActiveRecord\Base');
    $this->assert_instance_of($product, 'Misago\ActiveRecord\Record');
    
    $product = new Product(array('name' => 'azerty', 'price' => 18.99));
    $this->assert_equal($product->name, 'azerty');
    $this->assert_equal($product->price, 18.99);
    
    $this->fixtures('products');
    $product = new Product(1);
    $this->assert_equal($product->name, 'bepo');
  }
  
  function test_create()
  {
    $this->truncate('products');
    
    $product = Product::create(array('name' => 'azerty', 'price' => 9.95));
    
    $this->assert_equal(get_class($product), 'Product');
    $this->assert_equal($product->name, 'azerty');
    $this->assert_equal($product->price, 9.95);
    $this->assert_type($product->id, 'integer');
  }
  
  function test_create_many()
  {
    $data1 = array('name' => "qwerty", 'price' =>  5.98);
    $data2 = array('name' => "bepo",   'price' => 10.55);
  
    $products = Product::create($data1, $data2);
    
    $this->assert_equal(get_class($products[0]), 'Product');
    $this->assert_equal($products[1]->name, 'bepo');
    $this->assert_equal($products[0]->price, 5.98);
  }
  
  function test_find_all()
  {
    $this->fixtures('products');
    
    $products = Product::find();
    $this->assert_equal(count($products), 3);
    $this->assert_instance_of($products[0], 'Product');
    
    $products_more = Product::find(':all');
    $this->assert_equal(count($products), 3);
    $this->assert_equal($products_more[0]->id, $products[0]->id);
  }
  
  function test_find_one()
  {
    $options = array('conditions' => array('id' => 2));
    $product = Product::find(':first', $options);
    
    $this->assert_instance_of($product, 'Product');
    $this->assert_equal($product->name, 'qwerty');
    $this->assert_type($product->price, 'double');
    $this->assert_equal($product->price, 4.98);
  }
  
  function test_find_all_with_limit()
  {
    $options  = array('limit' => 2);
    $products = Product::find(':all', $options);
    $this->assert_instance_of($products[0], 'Product');
    $this->assert_equal(count($products), 2);
    
    $options  = array('limit' => 2, 'page' => 2);
    $products = Product::find(':all', $options);
    $this->assert_equal(count($products), 1);
  }
  
  function test_find_with_order()
  {
    $options = array('order' => 'name desc');
    $products = Product::find(':all', $options);
    $this->assert_equal($products[1]->name, 'bepo');
    
    $options = array('order' => 'products.name desc');
    $products = Product::find(':all', $options);
    $this->assert_equal($products[1]->name, 'bepo');
    
    $options = array('order' => 'name desc');
    $product = Product::find(':first', $options);
    $this->assert_equal($product->name, 'qwerty');
  }
  
  function test_with_all_options()
  {
    $options = array('conditions' => 'id > 1', 'order' => 'name desc');
    $products = Product::find(':all', $options);
    $this->assert_equal(count($products), 2);
    
    $options = array('conditions' => 'id > 1', 'order' => 'id asc', 'limit' => 1);
    $products = Product::find(':all', $options);
    $this->assert_equal(count($products), 1);
    $this->assert_equal($products[0]->id, 2, "skipped first result");
  }
  
  function test_find_id()
  {
    $product = Product::find(3);
    $this->assert_equal($product->id, 3);
    $this->assert_equal($product->name, 'azerty');
    
    $product = new Product(2);
    $this->assert_equal($product->id, 2);
    $this->assert_equal($product->name, 'qwerty');
  }
  
  function test_find_with_select()
  {
    $product = Product::find(':first', array('select' => 'id'));
    $this->assert_true(isset($product->id));
    $this->assert_false(isset($product->name));
  }
  
  function test_find_shortcuts()
  {
    $products = Product::all();
    $this->assert_equal(count($products), 3);
    
    $product = Product::first();
    $this->assert_instance_of($product, 'Product');
  }
  
  function test_find_by_sql()
  {
    $products = Product::find_by_sql('select * from products');
    $this->assert_type($products, 'array');
    $this->assert_equal(count($products), 3);
    $this->assert_instance_of($products[0], 'Product');

    $products = Product::find_by_sql('select id from products where id = 2');
    $this->assert_equal(count($products), 1);
    $this->assert_equal($products[0]->id, 2);
  }
  
  function test_count_by_sql()
  {
    $this->assert_equal(Product::count_by_sql('select count(*) from products'), 3);
    $this->assert_equal(Product::count_by_sql('select count(*) from products where id = 1'), 1);
  }
  
  function test_update()
  {
    $product = Product::update(1, array('name' => 'swerty'));
    $this->assert_equal(get_class($product), 'Product');
    $this->assert_equal($product->name, 'swerty', "attribute has changed");
    
    $product = Product::update(1, array('name' => 'swerty2', 'some_virtual_field' => ''));
    $product = new Product(1);
    $this->assert_equal($product->name, 'swerty2');
  }
  
  # TEST: Test failures when updating many records.
  function test_update_many()
  {
    $products = Product::update(array(1, 2), array(array('name' => 'swerty'), array('name' => 'bepo')));
    $this->assert_equal(get_class($products[0]), 'Product');
    $this->assert_equal($products[1]->name, 'bepo');
    $this->assert_equal($products[0]->name, 'swerty');
  }
  
  function test_update_all()
  {
    $updates = array('updated_at' => '2008-12-21 00:01:00');
    Product::update_all($updates);
    $products = Product::all();
    $this->assert_equal(
      array((string)$products[0]->updated_at, (string)$products[1]->updated_at, (string)$products[2]->updated_at),
      array('2008-12-21 00:01:00', '2008-12-21 00:01:00', '2008-12-21 00:01:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:02:00');
    Product::update_all($updates, 'id = 1');
    $products = Product::all();
    $this->assert_equal(
      array((string)$products[0]->updated_at, (string)$products[1]->updated_at, (string)$products[2]->updated_at),
      array('2008-12-21 00:02:00', '2008-12-21 00:01:00', '2008-12-21 00:01:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:04:00');
    Product::update_all($updates, null, array('limit' => 2, 'order' => 'id desc'));
    $products = Product::all();
    $this->assert_equal(
      array((string)$products[0]->updated_at, (string)$products[1]->updated_at, (string)$products[2]->updated_at),
      array('2008-12-21 00:02:00', '2008-12-21 00:04:00', '2008-12-21 00:04:00')
    );
    
    $updates = array('updated_at' => '2008-12-21 00:03:00');
    Product::update_all($updates, null, array('limit' => 1, 'order' => 'id asc'));
    $products = Product::all();
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
    
    $product = new Product(102);
    $this->assert_null($product->id);
    $this->assert_true($product->new_record());
    
    $products = $product->all();
    $this->assert_false($products[0]->new_record());
  }
  
  function test_save()
  {
    # save: create
    $product = new Product(array('name' => 'mwerty', 'price' => 6));
    $this->assert_true($product->new_record, "before creation: is a new record");
    $this->assert_true($product->save(), "creates record");
    $this->assert_false($product->new_record, "after creation: no longer a new record");
    
    # save: update
    $product->name = 'pwerty';
    $this->assert_true($product->save(), 'updates record');
  }
  
  function test_delete()
  {
    $this->fixtures('products');
    
    Product::delete(1);
    $this->assert_equal(product::find(1), null);
    
    $product = new Product(2);
    $product->delete();
    $this->assert_equal(product::find(2), null);
  }
  
  function test_delete_all()
  {
    $this->fixtures('products');
    
    Product::delete_all();
    $products = Product::all();
    $this->assert_equal(count($products), 0);
    
    $this->fixtures('products');
    
    Product::delete_all('id = 1');
    $product = Product::first(array('order' => 'id asc'));
    $this->assert_equal($product->name, 'qwerty', "delete_all with conditions");
    
    $this->fixtures('products');
    
    Product::delete_all(null, array('limit' => 1));
    $products = Product::all();
    $this->assert_equal(count($products), 2, "delete_all with limit");
    
    $this->fixtures('products');
    
    Product::delete_all(null, array('limit' => 2, 'order' => 'id desc'));
    $product = Product::first();
    $this->assert_equal($product->name, 'bepo', "delete_all with limit+order");
  }
  
  function test_destroy()
  {
    $this->fixtures('products');
    $product = new Product();
    
    Product::destroy(2);
    $this->assert_equal(Product::find(2), null);
    
    $product = new Product(1);
    $product->destroy();
    $this->assert_equal(Product::find(1), null);
  }
  
  function test_destroy_all()
  {
    $this->fixtures('products');
    
    Product::destroy_all();
    $products = Product::all();
    $this->assert_equal(count($products), 0);
    
    $this->fixtures('products');
    
    Product::destroy_all('id = 1');
    $product = Product::first(array('order' => 'id asc'));
    $this->assert_equal($product->name, 'qwerty', 'with conditions');
    
    $this->fixtures('products');
    
    Product::destroy_all(null, array('limit' => 1));
    $products = Product::all();
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
    $this->fixtures('products', 'orders', 'baskets', 'invoices');
  	
    $products = Product::find(':all', array(
      'select'     => 'products.id',
      'conditions' => 'baskets.order_id = 1',
      'joins'      => 'INNER JOIN baskets ON baskets.product_id = products.id',
    ));
    $this->assert_equal(count($products), 3);
    $this->assert_equal(array($products[0]->id, $products[1]->id, $products[2]->id), array(1, 2, 3));
    
    $products = Product::find(':all', array(
      'select'     => 'products.id',
      'conditions' => array('orders.id' => 1),
      'joins'      => array(
        'INNER JOIN baskets ON baskets.product_id = products.id',
        'INNER JOIN orders  ON orders.id = baskets.order_id'
      ),
    ));
    $this->assert_equal(count($products), 3);
    $this->assert_equal(array($products[0]->id, $products[1]->id, $products[2]->id), array(1, 2, 3));
    
    $baskets = Basket::find(':values', array(
      'select'     => 'baskets.id',
      'joins'      => 'order',
      'conditions' => 'orders.id = 1',
    ));
    $this->assert_equal(count($baskets), 3);
    $this->assert_equal(array($baskets[0][0], $baskets[1][0], $baskets[2][0]), array(2, 1, 3));
  }
  
  function test_exists()
  {
    $this->assert_true(Product::exists(1));
    $this->assert_false(Product::exists(512));
  }
  
  function test_magic_find_methods()
  {
    $products = Product::find_all_by_name('azerty');
    $this->assert_equal(count($products), 1);
    $this->assert_equal($products[0]->id, 3);
    
    $product = Product::find_by_id(1);
    $this->assert_equal($product->name, 'bepo');
    
    $products = Product::find_all();
    $this->assert_equal(count($products), 3);
    
    $products = Product::find_first();
    $this->assert_equal(count($products), 1);
    
    $products = Product::find_all(array('limit' => 1, 'page' => 2, 'order' => 'id asc'));
    $this->assert_equal(count($products), 1);
    $this->assert_equal($products[0]->id, 2);
  }
  
  function test_magic_count_methods()
  {
    $this->assert_equal(Product::count_by_name('azerty'), 1);
    $this->assert_equal(Product::count_by_id(1), 1);
  }
  
  function test_find_values()
  {
    $options = Product::find(':values', array('select' => 'name, id', 'order' => 'name asc'));
    
    $this->assert_equal(count($options), 3);
    $this->assert_equal($options[0][0], 'azerty');
    $this->assert_equal($options[0][1], '3');
    $this->assert_equal($options[1][0], 'bepo');
    $this->assert_equal($options[1][1], '1');

    $options = Product::values(array('select' => 'name, id', 'order' => 'name asc'));
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
    $a = array();
    $b = array();
    $c = Programmer::merge_options($a, $b);
    $this->assert_equal($c, array());
    
    $a = array('limit' => 10, 'select' => 'a.*');
    $b = array();
    $c = Programmer::merge_options($a, $b);
    $this->assert_equal($c, array('limit' => 10, 'select' => 'a.*'));
    
    $a = array();
    $b = array('conditions' => 'a = 1', 'limit' => 100);
    $c = Programmer::merge_options($a, $b);
    $this->assert_equal($c, array('conditions' => 'a = 1', 'limit' => 100));
    
    $a = array('conditions' => "b <> 'aze'");
    $b = array('conditions' => 'a = 1', 'limit' => 100);
    $c = Programmer::merge_options($a, $b);
    $this->assert_equal($c, array('conditions' => "(b <> 'aze') AND (a = 1)", 'limit' => 100));
    
    $a = array('conditions' => "b <> 'aze'");
    $b = array('conditions' => array('a = :a', array('a' => 12)), 'limit' => 100);
    $c = Programmer::merge_options($a, $b);
    $this->assert_equal($c, array('conditions' => "(b <> 'aze') AND (a = '12')", 'limit' => 100));
    
    $a = array('order' => 'created_at asc');
    $b = array('order' => 'created_at desc');
    $c = Programmer::merge_options($a, $b);
    $this->assert_equal($c, array('order' => 'created_at desc'));
  }
  
  function test_find_with_default_scope()
  {
    $invoices = Invoice::find(':all');
    
    # result is ordered (because of default_scope)
    $this->assert_equal($invoices[0]->id, 2);
    $this->assert_equal($invoices[1]->id, 1);
  }
  
  function test_eager_loading_with_default_scope()
  {
    $orders = Order::find(':all', array('conditions' => 'id = 1', 'include' => 'baskets'));
    
    # result is ordered (because of default_scope)
    $this->assert_equal($orders[0]->baskets[0]->id, 2);
    $this->assert_equal($orders[0]->baskets[1]->id, 1);
    $this->assert_equal($orders[0]->baskets[2]->id, 3);
  }
  
  function test_human_name()
  {
    $this->assert_equal(Order::human_name(),      'Order',    'defaults to String::humanize()');
    $this->assert_equal(Monitoring::human_name(), 'Guardian', 'specified human name (I18n)');
  }
  
  function test_human_attribute_name()
  {
    $this->assert_equal(Monitoring::human_attribute_name('title'), 'Title', 'defaults to humanize()');
    $this->assert_equal(Monitoring::human_attribute_name('length_string'), 'Length string');
    $this->assert_equal(Monitoring::human_attribute_name('length_string2'), 'My length', 'specified translation (I18n)');
  }
  
  function test_cache_key()
  {
    $order = new Order();
    $this->assert_equal($order->cache_key, 'orders/new');
    $this->assert_equal(Order::find(1)->cache_key, 'order/1-20091005124536');
    
    $this->fixtures('programmers');
    
    $programmer = new Programmer(2);
    $this->assert_equal($programmer->cache_key, 'programmer/2');
  }
  
  function test_toggle()
  {
    $this->fixtures('books');
    
    # true > false
    $book = new Book(1);
    $this->assert_true($book->published);
    $this->assert_true($book->toggle('published'));
    $book = new Book(1);
    $this->assert_false($book->published);
    
    # false > true
    $book = new Book(2);
    $this->assert_false($book->published);
    $this->assert_true($book->toggle('published'));
    $book = new Book(2);
    $this->assert_true($book->published);
    
    # new record
    $book = new Book(array('published' => false));
    $this->assert_nothing_thrown(function() use($book){
      $book->toggle('published');
    });
    $this->assert_true($book->published);
    
    # not a boolean column
    $book = new Book(array('title' => 'azerty'));
    $this->assert_throws('Misago\ActiveRecord\RecordNotSaved', function() use($book) {
      $book->toggle('title');
    });
    $this->assert_equal($book->title, 'azerty');
  }
  
  function test_increment()
  {
    $this->fixtures('posts');
    
    $post = new Post(1);
    $this->assert_equal($post->increment('comment_count'), 2);
    
    $post = new Post();
    $this->assert_equal($post->increment('comment_count'), 1);
    
    $this->assert_throws('Misago\ActiveRecord\RecordNotSaved', function() use($post) {
      $post->increment('title');
    });
  }
  
  function test_decrement()
  {
    $this->fixtures('posts');
    
    $post = new Post(1);
    $this->assert_equal($post->decrement('comment_count'), 0);
    
    $post = new Post();
    $this->assert_equal($post->decrement('comment_count'), -1);
    
    $this->assert_throws('Misago\ActiveRecord\RecordNotSaved', function() use($post) {
      $post->decrement('title');
    });
  }
  
  function test_increment_decrement_counter()
  {
    $this->fixtures('posts');
    
    Post::increment_counter('comment_count', 1);
    $post = new Post(1);
    $this->assert_equal($post->comment_count, 2);
    
    Post::decrement_counter('comment_count', 1);
    $post = new Post(1);
    $this->assert_equal($post->comment_count, 1);
  }
  
  # IMPROVE: Test ActiveRecord\Base::paginate() with 'count' option.
  function test_paginate()
  {
    $posts = Post::paginate(array('per_page' => 2));
    $this->assert_equal(count($posts), 2);
    
    $posts = Post::paginate(array('per_page' => 2, 'page' => 2));
    $this->assert_equal(count($posts), 1);
    
    $posts = Post::paginate(array('select' => 'id', 'per_page' => 2, 'page' => 2, 'order' => 'id desc'));
    $this->assert_equal(count($posts), 1);
    $this->assert_equal($posts[0]->id, 1);
    
    $posts = Post::paginate(array('conditions' => array('id' => 2)));
    $this->assert_equal(count($posts), 1);
    $this->assert_equal($posts[0]->id, 2);
    
    $posts = Post::paginate(array('conditions' => array('id' => 2), 'page' => 2));
    $this->assert_equal(count($posts), 0);
  }
}

?>
