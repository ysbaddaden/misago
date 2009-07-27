<?php

$_SERVER['MISAGO_ENV'] = 'test';
require_once dirname(__FILE__)."/../test_app/config/boot.php";

class Test_I18n extends Unit_Test
{
  function test_translate()
  {
    $this->assert_equal('simple', I18n::translate('toto'), 'Toto');
    $this->assert_equal('text',   I18n::translate('invalid title'), 'Title is invalid');
    $this->assert_equal('symbol', I18n::translate('invalid'), '{{attribute}} is invalid');
    $this->assert_equal('unknown key', I18n::translate('some_invalid_key'), 'some_invalid_key');
  }
  
  function test_do_translate()
  {
    $this->assert_equal('simple', I18n::do_translate('toto'), 'Toto');
    $this->assert_equal('text',   I18n::do_translate('invalid title'), 'Title is invalid');
    $this->assert_null('unknown key', I18n::do_translate('some_invalid_key'));
  }
  
  function test_translate_in_context()
  {
    $this->assert_equal('array 1', I18n::translate('empty', array('context' => 'active_record.errors.messages')), "{{attribute}} can't be empty");
    $this->assert_equal('array 2', I18n::translate('null',  array('context' => 'active_record.products.errors')), "{{attribute}} can't be null");
    $this->assert_equal('context in string', I18n::translate('active_record.products.errors.null'), "{{attribute}} can't be null");
    $this->assert_equal('unknown key in unknown context', I18n::translate('some_invalid_context.some_invalid_key'), 'some_invalid_context.some_invalid_key');
  }
  
  function test_do_translate_in_context()
  {
    $this->assert_equal('array 1', I18n::do_translate('empty', array('context' => 'active_record.errors.messages')), "{{attribute}} can't be empty");
    $this->assert_null('unknown key in unknown context', I18n::do_translate('some_invalid_context.some_invalid_key'));
  }
  
  function test_interpolation()
  {
    $this->assert_equal('interpolation with context', I18n::translate('active_record.products.errors.null', array('attribute' => 'name')), "name can't be null");
    $this->assert_equal('interpolation', I18n::translate('interpolation', array('bar' => 'baz')), "foo baz");
    $this->assert_equal('interpolation with context in string', I18n::translate('messages.foo', array('bar' => 'bat')), "foo bat");
  }
  
  function test_t()
  {
    $this->assert_equal('simple', t('toto'), 'Toto');
    $this->assert_equal('text',   t('invalid title'), 'Title is invalid');
    $this->assert_equal('symbol', t('invalid'), '{{attribute}} is invalid');
    $this->assert_equal('interpolation', t('interpolation', array('bar' => 'baz')), "foo baz");
  }
  
  function test_t_in_context()
  {
    $this->assert_equal('array 1', t('empty', array('context' => 'active_record.errors.messages')), "{{attribute}} can't be empty");
    $this->assert_equal('array 2', t('null',  array('context' => 'active_record.products.errors')), "{{attribute}} can't be null");
    
    $this->assert_equal('direct 1', t('empty', 'active_record.errors.messages'), "{{attribute}} can't be empty");
    $this->assert_equal('direct 2', t('null', 'active_record.products.errors'), "{{attribute}} can't be null");

    $this->assert_equal('interpolation', t('foo', array('context' => 'messages', 'bar' => 'baz')), "foo baz");
    $this->assert_equal('interpolation', t('messages.foo', array('bar' => 'bad')), "foo bad");
  }
}

new Test_I18n();

?>
