<?php

class Author extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
    static::has_many('authorships');
    static::has_many('books', array('through' => 'authorships'));
  }
}

?>
