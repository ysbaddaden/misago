<?php

class Book extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
    static::has_many('authorships');
    static::has_many('authors', array('through' => 'authorships'));
  }
}

?>
