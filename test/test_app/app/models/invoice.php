<?php

class Invoice extends ActiveRecord_Base
{
  protected $belongs_to = array('order');
}

?>
