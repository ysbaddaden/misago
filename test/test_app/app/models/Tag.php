<?php

class Tag extends Misago\ActiveRecord\Base
{
#  protected $belongs_to = array('post');

  static function __constructStatic()
  {
    static::has_one('post');
  }
}

?>
