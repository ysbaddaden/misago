<?php

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../test_app/config/boot.php';

class Test_ActiveRecord_Ephemeral extends Unit_TestCase
{
  function test_construct()
  {
    $contact = new Contact();
    $this->assert_equal('empty', $contact->attributes(), array());
    
    $contact->subject = 'some subject';
    $this->assert_equal('added attribute', $contact->attributes(), array('subject' => "some subject"));
    
    $contact->message = 'an important message';
    $this->assert_equal('added another attribute', $contact->attributes(),
      array('subject' => "some subject", 'message' => 'an important message'));
  }
  
  function test_human_name()
  {
    $contact = new Contact();
    $this->assert_equal('', $contact->human_name(), "Contact");
  }
  
  function test_human_attribute_name()
  {
    $contact = new Contact();
    $this->assert_equal('', $contact->human_attribute_name('subject'), "Subject");
    $this->assert_equal('', $contact->human_attribute_name('from_name'), "Your name");
  }
  
  function test_validate()
  {
    $contact = new Contact();
    $this->assert_false('missing required attributes', $contact->is_valid());
    
    $contact = new Contact(array('subject' => 'aaa', 'message' => 'bbb', 'from_name' => 'ccc', 'from_email' => 'toto@domain.com'));
    $this->assert_true('all required attributes are defined', $contact->is_valid());
    
    $contact = new Contact(array('subject' => str_repeat('a', 150)));
    $contact->is_valid();
    $this->assert_true('testing columns definition (limit)', $contact->errors->is_invalid('subject'));
  }
}

new Test_ActiveRecord_Ephemeral();

?>
