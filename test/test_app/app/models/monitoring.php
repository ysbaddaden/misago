<?php

class Monitoring extends ActiveRecord_Base
{
  protected $validates_presence_of = array(
    'title',
    'description' => array('on' => 'update', 'message' => 'There must be a description.'),
  );
  protected $validates_length_of = array(
    'length_string',
    'length_string2' => array('minimum' => 5),
    'length_minmax'  => array('minimum' => 20, 'maximum' => 2500),
    'length_is'      => array('is' => 40),
    'length_within'  => array('within' => '18..99'),
    'length_date'    => array('minimum' => '2009-04-15', 'maximum' => '2010-04-15'),
  );
}

?>
