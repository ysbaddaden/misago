<?php

$location = dirname(__FILE__).'/../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Associations extends Unit_TestCase
{
  function test_belongs_to_relationship()
  {
    $this->fixtures("products, orders, baskets, invoices");
    
    $invoice = new Invoice(1);
    $this->assert_instance_of('relation is the association', $invoice->order, 'Order');
    $this->assert_equal('must have loaded the right entry', $invoice->order->id, 1);
    
    $basket = new Basket(5);
    $this->assert_equal('must have loaded the right entry (bis)', $basket->product->id, 3);
  }
  
  function test_has_one_relationship()
  {
    $order = new Order(2);
    $this->assert_instance_of('relation is the association', $order->invoice, 'Invoice');
    $this->assert_equal('must have loaded the right entry', $order->invoice->id, 2);
  }
  
  function test_has_many_relationship()
  {
    $order = new Order(1);
    $this->assert_instance_of('relation is a collection', $order->baskets, 'ActiveRecord_Collection');
    $this->assert_equal('must have loaded associated entries', count($order->baskets), 3);
    
    $basket = $order->baskets->build();
    $this->assert_instance_of('collection must be related to the relation', $basket, 'Basket');
  }
  
  function test_has_and_belongs_to_many_relationship()
  {
    $this->fixtures('programmers, projects, programmers_projects');
    
    $programmer = new Programmer(1);
    $this->assert_instance_of('', $programmer->projects, 'ActiveRecord_Collection');
    $this->assert_equal('must have loaded associated entries', count($programmer->projects), 2);
    
    $project = $programmer->projects->build();
    $this->assert_instance_of('collection must be related to the relation', $project, 'Project');
  }
  
  function test_loading_association_when_parent_is_a_new_record()
  {
    $order = new Order(3);
    $this->assert_instance_of('has_one: must return the associated object', $order->invoice, 'Invoice');
    $this->assert_null('has_one: association must be a fresh object', $order->invoice->id);

    $tag = new Tag();
    $this->assert_instance_of("belongs_to: relationship", $tag->post, 'Post');
    $this->assert_null('belongs_to: fresh object', $tag->post->id);
    
    $post = new Post();
    $this->assert_instance_of("has_many: collection", $post->tags, 'ActiveRecord_Collection');
    $this->assert_equal('has_one: collection must be empty', $post->tags->count(), 0);
    $tag = $post->tags->build(array('tag' => 'aaa'));
    $this->assert_instance_of('has_one: collection must be related to the relation', $tag, 'Tag');
    
    $programmer = new Programmer();
    $this->assert_instance_of("HABTM: collection", $programmer->projects, 'ActiveRecord_Collection');
    $this->assert_equal('HABTM: collection must be empty', $programmer->projects->count(), 0);
    $project = $programmer->projects->build(array('name' => 'aaa'));
    $this->assert_instance_of('HABTM: collection must be related to the relation', $project, 'Project');
  }
  
  
  function test_eager_loading_for_belongs_to()
  {
    $invoice  = new Invoice();
    $invoices = $invoice->find(':all', array('include' => 'order'));
    $this->assert_true("is loaded", isset($invoices[0]->order));
    $this->assert_true("is loaded", isset($invoices[1]->order));
    $this->assert_instance_of("instance of relation", $invoices[0]->order, 'Order');
  }
  
  function test_eager_loading_for_has_one()
  {
    $order  = new Order();
    $orders = $order->find(':all', array('include' => 'invoice'));
    $this->assert_true("is loaded", isset($orders[0]->invoice));
    $this->assert_true("is loaded", isset($orders[1]->invoice));
    $this->assert_instance_of("instance of relation", $orders[0]->invoice, 'Invoice');
    
    $this->assert_true("relation must be set even thought there is no relation (to avoid unnecessary requests)",
      property_exists($orders[2], 'invoice'));
  }

  function test_eager_loading_for_has_many()
  {
    $order  = new Order();
    $orders = $order->find(':all', array('include' => 'baskets'));
    $this->assert_true("is loaded", isset($orders[0]->baskets));
    $this->assert_true("is loaded", isset($orders[1]->baskets));
    $this->assert_instance_of("container", $orders[0]->baskets, 'ActiveRecord_Collection');
    $this->assert_instance_of("instance of relation", $orders[0]->baskets[0], 'Basket');
    $this->assert_instance_of("instance of empty relation", $orders[2]->baskets, 'ActiveRecord_Collection');
  }

  function test_eager_loading_for_has_and_belongs_to_many()
  {
    $programmer  = new Programmer();
    $programmers = $programmer->find(':all', array('include' => 'projects'));
    $this->assert_true("is loaded", isset($programmers[0]->projects));
    $this->assert_true("is loaded", isset($programmers[1]->projects));
    $this->assert_instance_of("container", $programmers[0]->projects, 'ActiveRecord_Collection');
    $this->assert_instance_of("instance of relation", $programmers[1]->projects[0], 'Project');
    $this->assert_instance_of("instance of empty relation", $programmers[2]->projects, 'ActiveRecord_Collection');
  }
  
  
  function test_build_other()
  {
    $order = new Order(1);
    $order->build_invoice();
    $this->assert_true('is built', property_exists($order, 'invoice'));
    $this->assert_instance_of('instance of relation', $order->invoice, 'Invoice');
    $this->assert_equal('relation id', $order->invoice->order_id, 1);
    
    $order = new Order(2);
    $order->build_invoice(array('title' => "aze"));
    $this->assert_equal('relation id', $order->invoice->order_id, 2);
    $this->assert_equal('passed attributes', $order->invoice->title, 'aze');
  }
  
  function test_create_other()
  {
    $order = new Order(3);
    $order->create_invoice(array('name' => 'brice', 'id' => 5));
    $this->assert_true('is created', property_exists($order, 'invoice'));
    $this->assert_instance_of('instance of relation', $order->invoice, 'Invoice');
    $this->assert_equal('relation id', $order->invoice->order_id, 3);
    $this->assert_equal('passed attributes', $order->invoice->name, 'brice');
  }
  
  function test_others_build()
  {
    $order  = new Order(1);
    $basket = $order->baskets->build(array(
      'product_id' => 2,
      'created_at' => '2008-09-12 16:10:08',
    ));
    $this->assert_true('', $basket->new_record);
    $this->assert_equal('', $order->baskets->count(), 4);
  }
  
  function test_others_create()
  {
    $order  = new Order(2);
    $basket = $order->baskets->create(array(
      'id' => 7,
      'product_id' => 2,
      'created_at' => '2008-09-12 16:10:08',
    ));
    $this->assert_false('', $basket->new_record);
    $this->assert_equal('', $order->baskets->count(), 2);
  }
  
  function test_others_clear()
  {
    $order = new Order(1);
    $this->assert_equal('', $order->baskets->count(), 3);
    $order->baskets->clear();
    $this->assert_equal('', $order->baskets->count(), 0);
  }
  
  function test_others_delete()
  {
    $this->fixtures('baskets');
    
    $order = new Order(1);
    $order->baskets->delete($order->baskets[0]);
    $this->assert_equal('collection has been reduced by 1', $order->baskets->count(), 2);
    
    $order = new Order(1);
    $this->assert_equal('record must have been deleted from database', $order->baskets->count(), 2);

    $this->fixtures('baskets');

    $order = new Order(1);
    $order->baskets->delete($order->baskets[0], $order->baskets[2]);
    $this->assert_equal('collection has been reduced by 2', $order->baskets->count(), 1);

    $order = new Order(1);
    $this->assert_equal('the 2 records must have been deleted from database', $order->baskets->count(), 1);
  }
  
  function test_others_delete_all()
  {
    $this->fixtures('baskets');
    
    $order = new Order(1);
    $order->baskets->delete_all();
    $this->assert_equal('collection is now empty', $order->baskets->count(), 0);
    
    $order = new Order(1);
    $this->assert_equal('records must have been deleted from database', $order->baskets->count(), 0);
  }
  
  function test_others_destroy_all()
  {
    $this->fixtures('baskets');
    
    $order = new Order(1);
    $order->baskets->destroy_all();
    $this->assert_equal('collection is now empty', $order->baskets->count(), 0);
    
    $order = new Order(1);
    $this->assert_equal('records must have been destroyed from database', $order->baskets->count(), 0);
  }
  
  function test_others_find()
  {
    $this->fixtures('baskets');
    $order = new Order(1);
    $this->assert_equal('default: return all others for parent', $order->baskets->find()->count(), 3);
    
    $order = new Order(1);
    $this->assert_equal('some options', $order->baskets->find(array('limit' => 1))->count(), 1);
    
    $order = new Order(1);
    $basket = $order->baskets->find(':first', array('conditions' => array('id' => 3)));
    $this->assert_equal('must have found record id=3', $basket->id, 3);
    
    $order = new Order(1);
    $basket = $order->baskets->find(':first', array('conditions' => array('id' => 4)));
    $this->assert_equal("record exists, but doesn't belongs to this parent, thus null", $basket, null);
  }
  
  # TEST: test_others_build_when_parent_is_new_record()
  function test_others_build_when_parent_is_new_record()
  {
    
  }
  
  # TEST: test_others_create_when_parent_is_new_record()
  function test_others_create_when_parent_is_new_record()
  {
    
  }
  
  # TEST: test_others_delete_when_parent_is_new_record()
  function test_others_delete_when_parent_is_new_record()
  {
    
  }
  
  # TEST: test_others_delete_all_when_parent_is_new_record()
  function test_others_delete_all_when_parent_is_new_record()
  {
    
  }
  
  # TEST: test_others_find_when_parent_is_new_record()
  function test_others_find_when_parent_is_new_record()
  {
    
  }
  
  function test_build_join_for()
  {
    $order = new Order();
    $this->assert_equal('join with has_one relationship', str_replace('`', '"', $order->build_join_for('invoice')),
      'inner join "invoices" on "invoices"."order_id" = "orders"."id"');
    
    $this->assert_equal('left outer join', str_replace('`', '"', $order->build_join_for('invoice', 'left outer')),
      'left outer join "invoices" on "invoices"."order_id" = "orders"."id"');
    
    $this->assert_equal('join with has_many relationship', str_replace('`', '"', $order->build_join_for('baskets')),
      'inner join "baskets" on "baskets"."order_id" = "orders"."id"');
    
    $this->assert_equal('left join', str_replace('`', '"', $order->build_join_for('baskets', 'left')),
      'left join "baskets" on "baskets"."order_id" = "orders"."id"');
    
    $basket = new Basket();
    $this->assert_equal('join with belongs_to relationship', str_replace('`', '"', $basket->build_join_for('product')),
      'inner join "products" on "products"."id" = "baskets"."product_id"');
    
    $this->assert_equal('inner join', str_replace('`', '"', $basket->build_join_for('product', 'inner')),
      'inner join "products" on "products"."id" = "baskets"."product_id"');
    
    $programmer = new Programmer();
    $this->assert_equal('join with HATBM relationship', str_replace('`', '"', $programmer->build_join_for('projects')),
      'inner join "programmers_projects" on "programmers_projects"."programmer_id" = "programmers"."id" '.
      'inner join "projects" on "projects"."id" = "programmers_projects"."project_id"');
    
    $this->assert_equal('outer join', str_replace('`', '"', $programmer->build_join_for('projects', 'outer')),
      'outer join "programmers_projects" on "programmers_projects"."programmer_id" = "programmers"."id" '.
      'outer join "projects" on "projects"."id" = "programmers_projects"."project_id"');
  }
  
  function test_dependent_nullifying()
  {
    $this->fixtures('orders,invoices,baskets');
    
    $order = new NullifyOrder(2);
    $order->delete();
    
    $invoice = new Invoice(2);
    $this->assert_null('has one foreign key has been nullified', $invoice->order_id);
    
    $invoice = new Invoice(1);
    $this->assert_equal("must have nullified associated object only", $invoice->order_id, 1);
    
    $basket = new Basket(4);
    $this->assert_null('has many foreign key has been nullified', $basket->order_id);
    
    $basket = new Basket(5);
    $this->assert_equal("must have nullified associated objects only", $basket->order_id, 3);
    
    
    $order = new NullifyOrder(1);
    $order->destroy();

    $invoice = new Invoice(1);
    $this->assert_equal("has one foreign key isn't nullified on destroy", $invoice->order_id, 1);
    
    $basket = new Basket(2);
    $this->assert_equal("has many foreign key isn't nullified on destroy", $basket->order_id, 1);
  }
  
  function test_dependent_destroy()
  {
    $this->fixtures('orders,invoices,baskets');
    
    $order = new DestroyOrder(1);
    $order->delete();
    $this->assert_false('invoice was destroyed', $order->invoice->exists(1));
    $this->assert_null('baskets were destroyed', $order->baskets->find(1));
    
    $order = new DestroyOrder(2);
    $order->destroy();
    $this->assert_true('invoice was not destroyed', $order->invoice->exists(2));
    $this->assert_equal('baskets were not destroyed', $order->baskets->find(4)->id, 4);
    
    $this->fixtures('orders,invoices');
    
    $invoice = new DestroyInvoice(1);
    $invoice->delete();
    $this->assert_false('order was destroyed', $invoice->order->exists(1));
  }
  
  function test_dependent_delete()
  {
    $this->fixtures('orders,invoices,baskets');
    
    $order = new DeleteOrder(1);
    $order->delete();
    $this->assert_false('invoice was deleted', $order->invoice->exists(1));
    $this->assert_null('baskets were deleted', $order->baskets->find(1));
    
    $order = new DeleteOrder(2);
    $order->destroy();
    $this->assert_true('invoice was not destroyed', $order->invoice->exists(2));
    $this->assert_equal('baskets were not destroyed', $order->baskets->find(4)->id, 4);

    $this->fixtures('orders,invoices');
    
    $invoice = new DeleteInvoice(1);
    $invoice->delete();
    $this->assert_false('order was destroyed', $invoice->order->exists(1));
  }
}

class NullifyOrder extends Order
{
  protected $table_name = "orders";
  protected $has_one  = array('invoice' => array('dependent' => 'nullify', 'foreign_key' => 'order_id'));
  protected $has_many = array('baskets' => array('dependent' => 'nullify', 'foreign_key' => 'order_id'));
}

class DeleteOrder extends Order
{
  protected $table_name = "orders";
  protected $has_one  = array('invoice' => array('dependent' => 'delete', 'foreign_key' => 'order_id'));
  protected $has_many = array('baskets' => array('dependent' => 'delete_all', 'foreign_key' => 'order_id'));
}

class DestroyOrder extends Order
{
  protected $table_name = "orders";
  protected $has_one  = array('invoice' => array('dependent' => 'destroy', 'foreign_key' => 'order_id'));
  protected $has_many = array('baskets' => array('dependent' => 'destroy', 'foreign_key' => 'order_id'));
}

class DeleteInvoice extends Invoice
{
  protected $table_name = "invoices";
  protected $belongs_to = array('order' => array('dependent' => 'delete'));
}

class DestroyInvoice extends Invoice
{
  protected $table_name = "invoices";
  protected $belongs_to = array('order' => array('dependent' => 'destroy'));
}


new Test_ActiveRecord_Associations();

?>
