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
#    $this->assert_instance_of("instance of relation", $invoices[0]->order, 'Order');
  }
}

new Test_ActiveRecord_Associations();

?>
