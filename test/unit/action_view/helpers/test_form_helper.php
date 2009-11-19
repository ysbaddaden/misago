<?php

$location = dirname(__FILE__).'/../../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/ActionView/Helpers/TagHelper.php";
require_once MISAGO."/lib/ActionView/Helpers/FormTagHelper.php";
require_once MISAGO."/lib/ActionView/Helpers/FormHelper.php";

class Test_ActionView_Helpers_FormHelper extends Unit_TestCase
{
  function test_options_for_select()
  {
    $options = array('bepo' => 1, 'qwerty' => 2, 'azerty' => 3);
    $test = options_for_select($options);
    $this->assert_equal($test, '<option value="1">bepo</option>'.
      '<option value="2">qwerty</option>'.
      '<option value="3">azerty</option>'
    );
    
    $options = array(array('yes', 1), array('no', 0));
    $test = options_for_select($options);
    $this->assert_equal($test, '<option value="1">yes</option><option value="0">no</option>');
    
    $this->fixtures('products');
    $product = new Product();
    
    $options = $product->find(':values', array('select' => 'name, id', 'order' => 'name asc'));
    $test = options_for_select($options);
    $this->assert_equal($test, '<option value="3">azerty</option>'.
      '<option value="1">bepo</option>'.
      '<option value="2">qwerty</option>'
    );
  }
}

new Test_ActionView_Helpers_FormHelper();

?>
