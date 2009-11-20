<?php

class Post extends Misago\ActiveRecord\Base
{
  protected $has_many = array('tags');
}

?>
