<?php

class Order extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
#    static::has_many('products', array('throught' => 'baskets'));
    static::has_many('baskets',  array('dependent' => 'delete_all'));
    static::has_one('invoice',   array('dependent' => 'destroy'));
  }
}

?>
