<?php

class Tag extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
    static::has_one('post');
  }
}

?>
