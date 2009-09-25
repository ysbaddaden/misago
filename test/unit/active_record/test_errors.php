<?php

$location = dirname(__FILE__).'/../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Errors extends Unit_TestCase
{
  function test_add()
  {
    $errors = new ActiveRecord_Errors();
    
    $errors->add('id');
    $this->assert_equal('', $errors->messages, array('id' => array(':invalid')));
    
    $errors->add('name');
    $this->assert_equal('', $errors->messages, array(
      'id'   => array(':invalid'),
      'name' => array(':invalid')
    ));
    
    $errors->add('name', ':blank');
    $this->assert_equal('', $errors->messages, array(
      'id'   => array(':invalid'),
      'name' => array(':invalid', ':blank')
    ));
  }
  
  function test_add_on_blank()
  {
    $errors = new ActiveRecord_Errors();
    
    $errors->add_on_blank('id');
    $this->assert_equal('', $errors->messages, array('id' => array(':blank')));
  }
  
  function test_add_on_empty()
  {
    $errors = new ActiveRecord_Errors();
    
    $errors->add_on_empty('id');
    $this->assert_equal('', $errors->messages, array('id' => array(':empty')));
  }
  
  function test_add_to_base()
  {
    $errors = new ActiveRecord_Errors();
    
    $errors->add_to_base('error message');
    $this->assert_equal('', $errors->base_messages, array('error message'));
    
    $errors->add_to_base('another error message');
    $this->assert_equal('', $errors->base_messages, array('error message', 'another error message'));
  }
  
  function test_count()
  {
    $errors = new ActiveRecord_Errors();
    $this->assert_equal('no errors', $errors->count(), 0);
    
    $errors->add('id');
    $this->assert_equal('error on one field', $errors->count(), 1);
    
    $errors->add('name');
    $this->assert_equal('errors on two fields', $errors->count(), 2);
    
    $errors->add_on_blank('id');
    $this->assert_equal('second error on a field', $errors->count(), 3);
    
    $errors->add_on_blank('id');
    $this->assert_equal('third error on a field', $errors->count(), 4);
    
    $errors->add_to_base('there was an error');
    $this->assert_equal('second error on a field', $errors->count(), 5);
  }
  
  function test_clear()
  {
    $errors = new ActiveRecord_Errors();
    $this->assert_equal('no errors', $errors->count(), 0);
    
    $errors->add('id');
    $errors->add_to_base('there was an error');
    $this->assert_equal('error on one field', $errors->count(), 2);
    
    $errors->clear();
    $this->assert_equal('errors where cleared', $errors->count(), 0);
  }
  
  function test_is_empty()
  {
    $errors = new ActiveRecord_Errors();
    $this->assert_true('no errors', $errors->is_empty());
    
    $errors->add('id');
    $errors->add_to_base('there was an error');
    $this->assert_false('there are errors', $errors->is_empty());
    
    $errors->clear();
    $this->assert_true('errors where cleared', $errors->is_empty());

    $errors->clear();
    $errors->add_to_base('there was an error');
    $this->assert_false('adding errors to base', $errors->is_empty());
  }
  
  function test_is_invalid()
  {
    $errors = new ActiveRecord_Errors();
    $this->assert_false('no errors', $errors->is_invalid('title'));
    
    $errors->add('title');
    $this->assert_true('there are errors', $errors->is_invalid('title'));
    
    $errors->clear();
    $this->assert_false('errors where cleared', $errors->is_invalid('title'));
  }
  
  function test_on()
  {
    $errors = new ActiveRecord_Errors();
    $this->assert_type('', $errors->on('title'), 'NULL');
    
    $errors->add('title');
    $this->assert_equal('', $errors->on('title'), 'Title is invalid');
    
    $errors->add('title', 'error msg');
    $this->assert_equal('', $errors->on('title'), array('Title is invalid', 'error msg'));
    
    $errors->clear();
    $this->assert_type('errors where cleared', $errors->on('title'), 'NULL');
  }
  
  function test_on_base()
  {
    $errors = new ActiveRecord_Errors();
    $this->assert_type('', $errors->on_base(), 'NULL');
    
    $errors->add_to_base('error msg');
    $this->assert_equal('', $errors->on_base(), 'error msg');
    
    $errors->add_to_base('another error msg');
    $this->assert_equal('', $errors->on_base(), array('error msg', 'another error msg'));
    
    $errors->clear();
    $this->assert_type('errors where cleared', $errors->on_base(), 'NULL');
  }
  
  function test_full_messages()
  {
    $errors = new ActiveRecord_Errors();
    $this->assert_equal('', $errors->full_messages(), array());
    
    $errors->add_to_base('generic error msg');
    $this->assert_equal('', $errors->full_messages(), array('generic error msg'));
    
    $errors->add('title', 'title is invalid');
    $this->assert_equal('', $errors->full_messages(), array('generic error msg', 'title is invalid'));
    
    $errors->add('title', 'title is blank');
    $this->assert_equal('', $errors->full_messages(), array('generic error msg', 'title is invalid', 'title is blank'));
    
    $errors->clear();
    $this->assert_equal('errors where cleared', $errors->full_messages(), array());
  }
  
  function test_translated_error_messages()
  {
    $errors = new ActiveRecord_Errors(new Monitoring());
    
    $errors->add('title2', ':required');
    $this->assert_equal('attribute as its own translation', $errors->on('title2'), 'please fill this');
    
    $errors->add('title3', ':required');
    $this->assert_equal('model has its own translation', $errors->on('title3'), 'Title3 in monitoring cannot be blank');
  }
  
  function test_translated_full_messages()
  {
    $errors = new ActiveRecord_Errors(new Monitoring());
    
    $errors->add('title2', ':required');
    $errors->add('title3', ':required');
    
    $test = $errors->full_messages();
    $this->assert_equal('', $test, array('please fill this', 'Title3 in monitoring cannot be blank'));
  }
  
  function test_translated_error_messages_with_interpolation()
  {
    $error = new Error();
    $error = $error->create(array(
      'title' => 'my title',
      'subtitle' => 'my sub-title',
    ));
    $errors = new ActiveRecord_Errors($error);
    
    $errors->add('title', ':taken');
    $this->assert_equal('attribute + value', $errors->on('title'), "Title 'my title' is already taken");
    
    $errors->add('subtitle', ':taken');
    $this->assert_equal('attribute + value (check)', $errors->on('subtitle'), "Subtitle 'my sub-title' is already taken");
    
    $errors->add('domain', ':reserved');
    $this->assert_equal('model', $errors->on('domain'), "Already reserved in Error");
  }
  
  function test_to_xml()
  {
    $error = new Error();
    $error = $error->create(array(
      'title' => 'my title',
      'domain' => '',
    ));
    $errors = new ActiveRecord_Errors($error);
    $errors->add_to_base('Error on record');
    $errors->add('title', ':taken');
    $errors->add('domain', ':reserved');
    
    $this->assert_equal('', $errors->to_xml(), '<?xml version="1.0" encoding="UTF-8"?>'.
      '<errors>'.
      "<error>Error on record</error>".
      "<error>Title 'my title' is already taken</error>".
      '<error>Already reserved in Error</error>'.
      '</errors>'
    );
  }
  
  function test_to_json()
  {
    $error = new Error();
    $error = $error->create(array(
      'title' => 'my title',
      'domain' => '',
    ));
    $errors = new ActiveRecord_Errors($error);
    $errors->add_to_base('Error on record');
    $errors->add('title', ':taken');
    $errors->add('domain', ':reserved');
    
    $this->assert_equal('', $errors->to_json(), 
      '["Error on record","Title \'my title\' is already taken","Already reserved in Error"]');
  }
}

new Test_ActiveRecord_Errors();

?>
