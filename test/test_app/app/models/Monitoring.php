<?php

class Monitoring extends Misago\ActiveRecord\Base
{
  static function __constructStatic()
  {
    static::validates_presence_of('title');
    
    static::validates_length_of('length_string');
    static::validates_length_of('length_string2', array('minimum' => 5));
    static::validates_length_of('length_minmax',  array('minimum' => 20, 'maximum' => 2500, 'too_short' => 'Too small', 'too_long' => 'Too big'));
    static::validates_length_of('length_is',      array('is' => 40));
    static::validates_length_of('length_is2',     array('is' => 50, 'wrong_length' => 'Your miss'));
    static::validates_length_of('length_within',  array('within' => '18..99'));
    static::validates_length_of('length_date',    array('minimum' => '2009-04-15', 'maximum' => '2010-04-15'));
    
    static::validates_inclusion_of('inclusion_string', array(
      'allow_blank' => true,
      'in' => array('azerty', 'qwerty', 'bepo'),
      'message' => 'This is bad.',
    ));
    static::validates_inclusion_of('inclusion_integer', array('in' => array(1, 3, 6)));
    
    static::validates_exclusion_of('exclusion_string', array(
      'allow_blank' => true,
      'in' => array('azerty', 'qwerty', 'bepo'),
      'message' => 'This is bad.',
    ));
    static::validates_exclusion_of('exclusion_integer', array('in' => array(1, 3, 6)));
    
    static::validates_uniqueness_of('title');
	  static::validates_uniqueness_of('email', array('message' => 'Too late.'));
	  
    static::validates_format_of('email',  array('with' => '/[\w\.\-_]+\@[\w\.\-_]+\.\w{2,}/'));
    static::validates_format_of('email2', array('with' => '/[\w\.\-_]+\@[\w\.\-_]+\.\w{2,}/', 'message' => 'Bad email.', 'allow_blank' => true));
  }
  
  protected function validate_on_update()
  {
    $this->validate_presence_of('description', array('message' => 'There must be a description.'));
  }
}

?>
