<?php

class Post extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
    static::has_many('tags');
  }
}

?>
