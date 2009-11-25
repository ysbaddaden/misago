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
  
  static function __constructStatic()
  {
    static::validates_presence_of('subject');
    static::validates_presence_of('message');
    static::validates_presence_of('from_name');
    static::validates_presence_of('from_email');
    
    static::validates_length_of('subject');
    static::validates_length_of('from_name');
    static::validates_length_of('from_email');
    static::validates_length_of('empty');
  }
}

?>
