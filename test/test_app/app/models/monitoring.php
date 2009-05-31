<?php

class Monitoring extends ActiveRecord_Base
{
  protected function validate()
  {
    $this->validate_presence_of('title');
    
    $this->validate_length_of('length_string');
    $this->validate_length_of('length_string2', array('minimum' => 5));
    $this->validate_length_of('length_minmax',  array('minimum' => 20, 'maximum' => 2500));
    $this->validate_length_of('length_is',      array('is' => 40));
    $this->validate_length_of('length_within',  array('within' => '18..99'));
    $this->validate_length_of('length_date',    array('minimum' => '2009-04-15', 'maximum' => '2010-04-15'));
    
    $this->validate_inclusion_of('inclusion_string', array(
      'allow_blank' => true,
      'in' => array('azerty', 'qwerty', 'bepo'),
      'message' => 'This is bad.',
    ));
    $this->validate_inclusion_of('inclusion_integer', array('in' => array(1, 3, 6)));
    
    $this->validate_exclusion_of('exclusion_string', array(
      'allow_blank' => true,
      'in' => array('azerty', 'qwerty', 'bepo'),
      'message' => 'This is bad.',
    ));
    $this->validate_exclusion_of('exclusion_integer', array('in' => array(1, 3, 6)));
  
    $this->validate_uniqueness_of('title');
	  $this->validate_uniqueness_of('email', array('message' => 'Too late.'));
	  
    $this->validate_format_of('email',  array('with' => '/[\w\.\-_]+\@[\w\.\-_]+\.\w{2,}/'));
    $this->validate_format_of('email2', array('with' => '/[\w\.\-_]+\@[\w\.\-_]+\.\w{2,}/', 'message' => 'Bad email.', 'allow_blank' => true));
  }
  
  protected function validate_on_update()
  {
    $this->validate_presence_of('description', array('message' => 'There must be a description.'));
  }
}

?>
