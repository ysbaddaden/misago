<?php

class Post extends ActiveRecord_Base
{
  protected $has_many  = array('tags');
  protected $behaviors = array('taggable' => array('tags'));
}

?>
