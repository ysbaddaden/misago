<?php

class Tag extends Misago\ActiveRecord\Base
{
  protected $belongs_to = array('post');
}

?>
