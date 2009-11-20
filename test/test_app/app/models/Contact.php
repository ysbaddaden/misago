<?php

class Contact extends Misago\ActiveRecord\Ephemeral
{
  protected $columns = array(
    'subject'    => array('type' => 'string', 'limit' => 100),
    'message'    => array('type' => 'string'),
    'from_name'  => array('type' => 'string', 'limit' => 60),
    'from_email' => array('type' => 'string', 'limit' => 60),
    'empty'      => array('type' => 'string', 'limit' => 0),
  );
  
  protected function validate()
  {
    $this->validate_presence_of('subject');
    $this->validate_presence_of('message');
    $this->validate_presence_of('from_name');
    $this->validate_presence_of('from_email');
    
    $this->validate_length_of('subject');
    $this->validate_length_of('from_name');
    $this->validate_length_of('from_email');
    $this->validate_length_of('empty');
  }
}

?>
