<?php

$location = dirname(__FILE__).'/../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";

class Test_ActiveRecord_Errors extends Unit_Test
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
}

new Test_ActiveRecord_Errors();

?>
