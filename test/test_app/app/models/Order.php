<?php

class Order extends Misago\ActiveRecord\Base
{
##  protected $has_many = array('products' => array('throught' => 'basket'));
#  protected $has_many = array('baskets' => array('dependent' => 'delete_all'));
#  protected $has_one  = array('invoice' => array('dependent' => 'destroy'));

  static function __constructStatic()
  {
#    static::has_many('products', array('throught' => 'baskets'));
    static::has_many('baskets',  array('dependent' => 'delete_all'));
    static::has_one('invoice',   array('dependent' => 'destroy'));
  }
}

?>
