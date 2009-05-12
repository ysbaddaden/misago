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
    $this->assert_instance_of('order->baskets', $order->baskets, 'ArrayAccess');
    $this->assert_equal('count', count($order->baskets), 3);
  }

  function test_eager_loading_for_belongs_to()
  {
    $invoice  = new Invoice();
    $invoices = $invoice->find(':all', array('include' => 'order'));
    $this->assert_true("is loaded", isset($invoices[0]->order));
    $this->assert_true("is loaded", isset($invoices[1]->order));
    $this->assert_instance_of("instance of relation", $invoices[0]->order, 'Order');
  }
  
  # FIXME: Test that $orders[2]->invoice has been set to 'null' (problem: can't use isset() on a nullified var).
  function test_eager_loading_for_has_one()
  {
    $order  = new Order();
    $orders = $order->find(':all', array('include' => 'invoice'));
    $this->assert_true("is loaded", isset($orders[0]->invoice));
    $this->assert_true("is loaded", isset($orders[1]->invoice));
    $this->assert_instance_of("instance of relation", $orders[0]->invoice, 'Invoice');
    
#    $this->assert_type("relation must be set even thought there is no relation (to avoid unnecessary requests)",
#      $orders[2]->invoice, 'NULL');
  }

  function test_eager_loading_for_has_many()
  {
    $order  = new Order();
    $orders = $order->find(':all', array('include' => 'baskets'));
    $this->assert_true("is loaded", isset($orders[0]->baskets));
    $this->assert_true("is loaded", isset($orders[1]->baskets));
    $this->assert_instance_of("container", $orders[0]->baskets, 'ArrayAccess');
    $this->assert_instance_of("instance of relation", $orders[0]->baskets[0], 'Basket');
    $this->assert_instance_of("instance of empty relation", $orders[2]->baskets, 'ArrayAccess');
  }
}

new Test_ActiveRecord_Associations();

?>
