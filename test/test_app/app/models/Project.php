<?php

class Project extends Misago\ActiveRecord\Base
{
  protected $has_and_belongs_to_many = array('programmers');
}

?>
