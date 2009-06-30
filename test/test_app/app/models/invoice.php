<?php

class Invoice extends ActiveRecord_Base
{
  protected $belongs_to    = array('order');
  protected $default_scope = array('order' => 'created_at desc');
}

?>
