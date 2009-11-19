<?php

class Tag extends ActiveRecord_Base
{
  protected $belongs_to = array('post');
}

?>
