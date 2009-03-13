<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/html.php";
require_once MISAGO."/lib/action_view/helpers/form.php";

class Test_ActionView_Helper_Form extends Unit_TestCase
{
  function test_options_for_select()
  {
    $options = array('bepo' => 1, 'qwerty' => 2, 'azerty' => 3);
    $test = form::options_for_select($options);
    $this->assert_equal('', $test, '<option value="1">bepo</option>'.
      '<option value="2">qwerty</option>'.
      '<option value="3">azerty</option>'
    );
    
    $this->fixtures('products');
    $product = new Product();
    
    $options = $product->find(':values', array('select' => 'name, id', 'order' => 'name asc'));
    $test = form::options_for_select($options);
    $this->assert_equal('', $test, '<option value="3">azerty</option>'.
      '<option value="1">bepo</option>'.
      '<option value="2">qwerty</option>'
    );
  }
}

new Test_ActionView_Helper_Form();

?>
