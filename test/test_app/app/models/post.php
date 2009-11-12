<?php

class Post extends ActiveRecord_Base
{
  protected $include_modules = array('ActiveRecord_Acts_Taggable_Base');
  protected $has_many        = array('tags');
}

?>
