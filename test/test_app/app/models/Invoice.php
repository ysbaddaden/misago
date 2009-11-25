<?php

class Invoice extends Misago\ActiveRecord\Base
{
  protected $default_scope = array('order' => 'created_at desc');
  
  static function __constructStatic()
  {
    static::belongs_to('order');
  }
}

?>
