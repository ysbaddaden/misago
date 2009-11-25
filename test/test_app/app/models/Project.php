<?php

class Project extends Misago\ActiveRecord\Base
{
#  protected $has_and_belongs_to_many = array('programmers');

  static function __constructStatic()
  {
    static::has_and_belongs_to_many('programmers');
  }
}

?>
