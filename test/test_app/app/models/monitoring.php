<?php

class Monitoring extends ActiveRecord_Base
{
  protected $validates_presence_of = array(
    'title',
    'description' => array('on' => 'update', 'message' => 'There must be a description.'),
  );
}

?>
