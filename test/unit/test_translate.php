<?php

require_once dirname(__FILE__)."/../test_app/config/boot.php";
require 'translate.php';

class Test_Translate extends Unit_Test
{
  function test_t()
  {
    $this->assert_equal('simple', t('toto'), 'Toto');
    $this->assert_equal('text', t('invalid title'), 'Title is invalid');
    $this->assert_equal('symbol', t('invalid'), '{{attribute}} is invalid');
  }
  
  function test_t_in_context()
  {
    
  }
  
  function test_t_with_vars()
  {
#    $this->assert_equal('simple', t('{{toto}}', null, array('toto' => 'Toto')), 'Toto');
#    $this->assert_equal('simple', t('{{attribute}} is invalid', null, array('attribute' => 'title')), 'title is invalid');
  }
}

new Test_Translate();

?>
