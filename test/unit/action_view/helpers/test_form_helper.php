<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/html_tag.php";
require_once MISAGO."/lib/action_view/helpers/form_helper.php";

class Test_ActionView_Helper_FormHelper extends Unit_TestCase
{
  function test_fields_for()
  {
    $f = fields_for('Product');
    $this->assert_instance_of("", $f, 'FormHelper');
    
    $product = new Product();
    $f = fields_for($product);
    $this->assert_instance_of("", $f, 'FormHelper');
  }
  
  function test_label()
  {
    $f = fields_for('Product');
    $this->assert_equal("", $f->label('available'), '<label for="product_available">Available</label>');
    $this->assert_equal("", $f->label('title'),     '<label for="product_title">Title</label>');
  }
  
  function test_check_box()
  {
    $f = fields_for('Product');
    $this->assert_equal("", $f->check_box('available'),
      '<input type="hidden" name="product[available]" value="0"/>'.
      '<input id="product_available" type="checkbox" name="product[available]" value="1"/>'
    );
    
    $this->assert_equal("", $f->check_box('available', array('class' => 'test')),
      '<input type="hidden" name="product[available]" value="0"/>'.
      '<input class="test" id="product_available" type="checkbox" name="product[available]" value="1"/>'
    );
  }
}

new Test_ActionView_Helper_FormHelper();

?>
