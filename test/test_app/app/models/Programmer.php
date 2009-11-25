<?php

class Programmer extends Misago\ActiveRecord\Base
{
#  protected $has_and_belongs_to_many = array('projects');

  static function __constructStatic()
  {
    static::has_and_belongs_to_many('projects');
  }
}

?>
