<?php

$_ENV['MISAGO_ENV'] = 'test';
require_once dirname(__FILE__)."/../test_app/config/boot.php";

class Test_I18n extends Unit_Test
{
  function test_t()
  {
    $this->assert_equal('simple', t('toto'), 'Toto');
    $this->assert_equal('text',   t('invalid title'), 'Title is invalid');
    $this->assert_equal('symbol', t('invalid'), '{{attribute}} is invalid');
  }
  
  function test_t_in_context()
  {
    $this->assert_equal('symbol', t('empty', 'active_record.errors.messages'), "{{attribute}} can't be empty");
    $this->assert_equal('symbol', t('null', 'active_record.products.errors'), "{{attribute}} can't be null");
  }
}

new Test_I18n();

?>
