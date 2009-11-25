<?php

class Post extends Misago\ActiveRecord\Base
{
#  protected $has_many = array('tags');
  
  static function __constructStatic()
  {
    static::has_many('tags');
  }
}

?>
