<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../test_app/config/boot.php';

class Test_ActiveRecord_Ephemeral extends Misago\Unit\TestCase
{
  function test_construct()
  {
    $contact = new Contact();
    $this->assert_equal($contact->attributes(), array());
    
    $contact->subject = 'some subject';
    $this->assert_equal($contact->attributes(), array('subject' => "some subject"));
    
    $contact->message = 'an important message';
    $this->assert_equal($contact->attributes(), array('subject' => "some subject", 'message' => 'an important message'));
  }
  
  function test_human_name()
  {
    $contact = new Contact();
    $this->assert_equal($contact->human_name(), "Contact");
  }
  
  function test_human_attribute_name()
  {
    $contact = new Contact();
    $this->assert_equal($contact->human_attribute_name('subject'), "Subject");
    $this->assert_equal($contact->human_attribute_name('from_name'), "Your name");
  }
  
  function test_validate()
  {
    $contact = new Contact();
    $this->assert_false($contact->is_valid(), 'missing required attributes');
    
    $contact = new Contact(array('subject' => 'aaa', 'message' => 'bbb', 'from_name' => 'ccc', 'from_email' => 'toto@domain.com'));
    $this->assert_true($contact->is_valid(), 'all required attributes are defined');
    
    $contact = new Contact(array('subject' => str_repeat('a', 150)));
    $contact->is_valid();
    $this->assert_true($contact->errors->is_invalid('subject'), 'testing columns definition (limit)');
  }
}

new Test_ActiveRecord_Ephemeral();

?>
