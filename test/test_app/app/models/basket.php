<?php

class Basket extends ActiveRecord_Base
{
  protected $belongs_to    = array('product', 'order');
  protected $default_scope = array('order' => 'created_at asc');
}

?>
