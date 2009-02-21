<?php

class Basket extends ActiveRecord_Base
{
  protected $belongs_to = array('product', 'order');
}

?>
