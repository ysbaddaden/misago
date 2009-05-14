<?php

class Project extends ActiveRecord_Base
{
  protected $has_and_belongs_to_many = array('programmers');
}

?>
