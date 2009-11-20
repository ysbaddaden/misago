<?php

class Invoice extends Misago\ActiveRecord\Base
{
  protected $belongs_to    = array('order');
  protected $default_scope = array('order' => 'created_at desc');
}

?>
