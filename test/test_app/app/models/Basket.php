<?php

class Basket extends Misago\ActiveRecord\Base
{
  protected $default_scope = array('order' => 'baskets.created_at asc');
  
  static function __constructStatic()
  {
    static::belongs_to('product');
    static::belongs_to('order');
  }
}

?>
