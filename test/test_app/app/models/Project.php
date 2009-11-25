<?php

class Project extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
    static::has_and_belongs_to_many('programmers');
  }
}

?>
