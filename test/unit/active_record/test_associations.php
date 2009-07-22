<?php

$location = dirname(__FILE__).'/../../..';
$_SERVER['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Associations extends Unit_TestCase
{
  function test_belongs_to_relationship()
  {
    $this->fixtures("products, orders, baskets, invoices");
    
    $invoice = new Invoice(1);
    $this->assert_instance_of('invoice->order', $invoice->order, 'Order');
    $this->assert_equal('invoice->order->id', $invoice->order->id, 1);
  }
  
  function test_has_one_relationship()
  {
    $order = new Order(2);
    $this->assert_instance_of('order->invoice', $order->invoice, 'Invoice');
    $this->assert_equal('order->invoice->id', $order->invoice->id, 2);
  }
  
  function test_has_many_relationship()
  {
    $order = new Order(1);
    $this->assert_instance_of('order->baskets', $order->baskets, 'ActiveRecord_Collection');
    $this->assert_equal('count', count($order->baskets), 3);
  }
  
  function test_has_and_belongs_to_many_relationship()
  {
    $this->fixtures('programmers, projects, programmers_projects');
    
    $programmer = new Programmer(1);
    $this->assert_instance_of('', $programmer->projects, 'ActiveRecord_Collection');
    $this->assert_equal('count', count($programmer->projects), 2);
  }
  
  
  function test_loading_association_on_non_saved_parent()
  {
    $order = new Order();
    $this->assert_instance_of('must return the associated object', $order->invoice, 'Invoice');
    $this->assert_null('association must be a fresh object', $order->invoice->id);
  }
  
  function test_loading_association_when_association_is_missing()
  {
    $order = new Order(3);
    $this->assert_instance_of('must return the associated object', $order->invoice, 'Invoice');
    $this->assert_null('association must be a fresh object', $order->invoice->id);
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
    $order->create_invoice(array('name' => 'brice'));
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
  
  # TODO: test_others_build_when_parent_is_new_record()
  function test_others_build_when_parent_is_new_record()
  {
    
  }
  
  # TODO: test_others_create_when_parent_is_new_record()
  function test_others_create_when_parent_is_new_record()
  {
    
  }
  
  # TODO: test_others_delete_when_parent_is_new_record()
  function test_others_delete_when_parent_is_new_record()
  {
    
  }
  
  # TODO: test_others_delete_all_when_parent_is_new_record()
  function test_others_delete_all_when_parent_is_new_record()
  {
    
  }
  
  # TODO: test_others_find_when_parent_is_new_record()
  function test_others_find_when_parent_is_new_record()
  {
    
  }
}

new Test_ActiveRecord_Associations();

?>
