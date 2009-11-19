<?php

class Post extends ActiveRecord_Base
{
  protected $has_many = array('tags');
}

?>
