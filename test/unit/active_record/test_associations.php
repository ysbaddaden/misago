<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

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
}

new Test_ActiveRecord_Associations();

?>
