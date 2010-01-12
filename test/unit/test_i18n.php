<?php
require_once __DIR__.'/../unit.php';
use Misago\I18n;
use Misago\ActiveSupport;

class Test_I18n extends Test\Unit\TestCase
{
  function test_translate()
  {
    $this->assert_equal(I18n::translate('toto'), 'Toto');
    $this->assert_equal(I18n::translate('invalid title'), 'Title is invalid');
    $this->assert_equal(I18n::translate('invalid'), '{{attribute}} is invalid');
    $this->assert_equal(I18n::translate('some_invalid_key'), 'some_invalid_key');
  }
  
  function test_do_translate()
  {
    $this->assert_equal(I18n::do_translate('toto'), 'Toto');
    $this->assert_equal(I18n::do_translate('invalid title'), 'Title is invalid');
    $this->assert_null(I18n::do_translate('some_invalid_key'));
  }
  
  function test_translate_in_context()
  {
    $this->assert_equal(I18n::translate('empty', array('context' => 'active_record.errors.messages')), "{{attribute}} can't be empty");
    $this->assert_equal(I18n::translate('null',  array('context' => 'active_record.products.errors')), "{{attribute}} can't be null");
    $this->assert_equal(I18n::translate('active_record.products.errors.null'), "{{attribute}} can't be null");
    $this->assert_equal(I18n::translate('some_invalid_context.some_invalid_key'), 'some_invalid_context.some_invalid_key');
  }
  
  function test_do_translate_in_context()
  {
    $this->assert_equal(I18n::do_translate('empty', array('context' => 'active_record.errors.messages')), "{{attribute}} can't be empty");
    $this->assert_null(I18n::do_translate('some_invalid_context.some_invalid_key'));
  }
  
  function test_interpolation()
  {
    $this->assert_equal(I18n::translate('active_record.products.errors.null', array('attribute' => 'name')), "name can't be null");
    $this->assert_equal(I18n::translate('interpolation', array('bar' => 'baz')), "foo baz");
    $this->assert_equal(I18n::translate('messages.foo',  array('bar' => 'bat')), "foo bat");
    $this->assert_equal(I18n::translate('foo {{bar}}',   array('bar' => 'bat')), "foo bat");
  }
  
  function test_t()
  {
    $this->assert_equal(t('toto'), 'Toto');
    $this->assert_equal(t('invalid title'), 'Title is invalid');
    $this->assert_equal(t('invalid'), '{{attribute}} is invalid');
    $this->assert_equal(t('interpolation', array('bar' => 'baz')), "foo baz");
  }
  
  function test_pluralize()
  {
    $this->assert_equal(t('there_are_x_messages', array('count' => 1)), 'there is one message');
    $this->assert_equal(t('there_are_x_messages', array('count' => 5)), 'there are 5 messages');
  }
  
  function test_t_in_context()
  {
    $this->assert_equal(t('empty', array('context' => 'active_record.errors.messages')), "{{attribute}} can't be empty");
    $this->assert_equal(t('null',  array('context' => 'active_record.products.errors')), "{{attribute}} can't be null");
    
    $this->assert_equal(t('empty', 'active_record.errors.messages'), "{{attribute}} can't be empty");
    $this->assert_equal(t('null', 'active_record.products.errors'), "{{attribute}} can't be null");
    
    # with interpolation
    $this->assert_equal(t('foo', array('context' => 'messages', 'bar' => 'baz')), "foo baz");
    $this->assert_equal(t('messages.foo', array('bar' => 'bad')), "foo bad");
    
    # plural
    $this->assert_equal(t('x_minutes', array('context' => 'plural', 'count' => 1)),  'a minute');
    $this->assert_equal(t('x_minutes', array('context' => 'plural', 'count' => 24)), '24 minutes');
  }
  
  function test_localize()
  {
    $this->assert_equal(l(new ActiveSupport\Date('2009-08-12')), '08/12/2009');
    $this->assert_equal(l(new ActiveSupport\Date('2009-08-12'),
      array('format' => 'short')), 'Aug 12');
    
    $this->assert_equal(l(new ActiveSupport\Time('13:45:36')), '01:45 PM');
    $this->assert_equal(l(new ActiveSupport\Time('13:45:36'),
      array('format' => 'short')), 'Tue, 12 Jan 01:45 PM');
    
    $this->assert_equal(l(new ActiveSupport\Datetime('2009-06-12 00:12:36')),
      'Fri, 12 Jun 2009 12:12:36 AM +0000');
    $this->assert_equal(l(new ActiveSupport\Datetime('2009-06-12 00:12:36'),
      array('format' => 'short')), 'Fri, 12 Jun 12:12 AM');
    $this->assert_equal(l(new ActiveSupport\Datetime('2009-06-12 00:12:36'),
      array('format' => 'long')), 'June 12, 2009 12:12 AM');
    $this->assert_equal(l(new ActiveSupport\Datetime('2009-06-12 00:12:36'),
      array('format' => 'long')), 'June 12, 2009 12:12 AM');
  }
}

?>
