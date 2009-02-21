<?php

class Order extends ActiveRecord_Base
{
#  protected $has_many = array('products' => array('throught' => 'basket'));
  protected $has_one  = array('invoice');
}

?>
