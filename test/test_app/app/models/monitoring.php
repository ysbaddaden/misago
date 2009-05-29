<?php

class Monitoring extends ActiveRecord_Base
{
  protected function validate()
  {
    $this->validates_presence_of('title');
    
    $this->validates_length_of('length_string');
    $this->validates_length_of('length_string2', array('minimum' => 5));
    $this->validates_length_of('length_minmax',  array('minimum' => 20, 'maximum' => 2500));
    $this->validates_length_of('length_is',      array('is' => 40));
    $this->validates_length_of('length_within',  array('within' => '18..99'));
    $this->validates_length_of('length_date',    array('minimum' => '2009-04-15', 'maximum' => '2010-04-15'));
    
    $this->validates_inclusion_of('inclusion_string', array(
      'allow_blank' => true,
      'in' => array('azerty', 'qwerty', 'bepo'),
      'message' => 'This is bad.',
    ));
    $this->validates_inclusion_of('inclusion_integer', array('in' => array(1, 3, 6)));
    
    $this->validates_exclusion_of('exclusion_string', array(
      'allow_blank' => true,
      'in' => array('azerty', 'qwerty', 'bepo'),
      'message' => 'This is bad.',
    ));
    $this->validates_exclusion_of('exclusion_integer', array('in' => array(1, 3, 6)));
  
    $this->validates_uniqueness_of('title');
	  $this->validates_uniqueness_of('email', array('message' => 'Too late.'));
	  
    $this->validates_format_of('email',  array('with' => '/[\w\.\-_]+\@[\w\.\-_]+\.\w{2,}/'));
    $this->validates_format_of('email2', array('with' => '/[\w\.\-_]+\@[\w\.\-_]+\.\w{2,}/', 'message' => 'Bad email.', 'allow_blank' => true));
  }
  
  protected function validate_on_update()
  {
    $this->validates_presence_of('description', array('message' => 'There must be a description.'));
  }
  
  /*
  protected $validates_length_of = array(
    'length_string',
    'length_string2' => array('minimum' => 5),
    'length_minmax'  => array('minimum' => 20, 'maximum' => 2500),
    'length_is'      => array('is' => 40),
    'length_within'  => array('within' => '18..99'),
    'length_date'    => array('minimum' => '2009-04-15', 'maximum' => '2010-04-15'),
  );
  
  protected $validates_inclusion_of = array(
    'inclusion_string' => array(
      'allow_blank' => true,
      'in' => array('azerty', 'qwerty', 'bepo'),
      'message' => 'This is bad.'
    ),
    'inclusion_integer' => array('in' => array(1, 3, 6)),
  );
  
  protected $validates_exclusion_of = array(
    'exclusion_string' => array(
      'allow_blank' => true,
      'in' => array('azerty', 'qwerty', 'bepo'),
      'message' => 'This is bad.'
    ),
    'exclusion_integer' => array('in' => array(1, 3, 6)),
  );
  
  protected $validates_format_of = array(
    'email'  => array('with' => '/[\w\.\-_]+\@[\w\.\-_]+\.\w{2,}/'),
    'email2' => array('with' => '/[\w\.\-_]+\@[\w\.\-_]+\.\w{2,}/', 'message' => 'Bad email.', 'allow_blank' => true),
  );
  
  protected $validates_presence_of = array(
    'title',
    'description' => array('on' => 'update', 'message' => 'There must be a description.'),
  );
  protected $validates_uniqueness_of = array(
	  'title',
	  'email' => array('message' => 'Too late.')
  );
  */
}

?>
