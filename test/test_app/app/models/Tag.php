<?php

class Tag extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
    static::belongs_to('post');
  }
}

?>
