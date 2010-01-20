<?php

class Authorship extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
    static::belongs_to('author');
    static::belongs_to('book');
  }
}

?>
