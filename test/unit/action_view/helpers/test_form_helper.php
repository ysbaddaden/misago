<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/html.php";
require_once MISAGO."/lib/action_view/helpers/form.php";
require_once MISAGO."/lib/action_view/helpers/form_helper.php";

class Test_ActionView_Helper_FormHelper extends Unit_TestCase
{
  function test_fields_for()
  {
    $f = fields_for('Product');
    $this->assert_instance_of("Passing a camelized string", $f, 'FormHelper');
    
    $f = fields_for('basket');
    $this->assert_instance_of("Passing an underscored string", $f, 'FormHelper');
    
    $product = new Product();
    $f = fields_for($product);
    $this->assert_instance_of("Passing an ActiveRecord", $f, 'FormHelper');
  }
  
  function test_label()
  {
    $f = fields_for('Product');
    $this->assert_equal("", $f->label('available'), '<label for="product_available">Available</label>');
    $this->assert_equal("", $f->label('title'),     '<label for="product_title">Title</label>');
    $this->assert_equal("", $f->label('username', 'User name'), '<label for="product_username">User name</label>');
    $this->assert_equal("", $f->label('username', 'User name', array('class' => 'username')), '<label class="username" for="product_username">User name</label>');
    $this->assert_equal("", $f->label('username', null, array('class' => 'username')), '<label class="username" for="product_username">Username</label>');
    $this->assert_equal("", $f->label('username', array('class' => 'username')), '<label class="username" for="product_username">Username</label>');
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
    
    $product = new Product(array('in_stock' => true));
    $f = fields_for($product);
    $this->assert_equal("", $f->check_box('in_stock'),
      '<input type="hidden" name="product[in_stock]" value="0"/>'.
      '<input id="product_in_stock" checked="checked" type="checkbox" name="product[in_stock]" value="1"/>'
    );
    
    $product = new Product(array('in_stock' => false));
    $f = fields_for($product);
    $this->assert_equal("", $f->check_box('in_stock', array('class' => 'checkbox')),
      '<input type="hidden" name="product[in_stock]" value="0"/>'.
      '<input class="checkbox" id="product_in_stock" type="checkbox" name="product[in_stock]" value="1"/>'
    );
  }
  
  function test_text_field()
  {
    $product = new Product(array('name' => 'azerty'));
    $f = fields_for($product);
    $this->assert_equal("", $f->text_field('name'),
      '<input id="product_name" type="text" name="product[name]" value="azerty"/>'
    );
    
    $product = new Product(array('name' => 'bepo'));
    $f = fields_for($product);
    $this->assert_equal("", $f->text_field('name', array('class' => 'text')),
      '<input class="text" id="product_name" type="text" name="product[name]" value="bepo"/>'
    );
  }
  
  function test_text_area()
  {
    $product = new Product(array('description' => 'some text'));
    $f = fields_for($product);
    $this->assert_equal("", $f->text_area('description'),
      '<textarea id="product_description" name="product[description]">some text</textarea>'
    );
    
    $product->description = 'some <html> content';
    $this->assert_equal("", $f->text_area('description', array('class' => 'text')),
      '<textarea class="text" id="product_description" name="product[description]">some &lt;html&gt; content</textarea>'
    );
  }
  
  # TODO: Fix radio_button name and id by appending value to them!
  function test_radio_button()
  {
    $product = new Product(array('price' => '9.95'));
    $f = fields_for($product);
    $product->assert_equal("", $f->radio_button('price', 9.95),
      '<input id="price" checked="checked" type="radio" name="price" value="9.95">');
    $product->assert_equal("", $f->radio_button('price', 5.95),
      '<input id="price" type="radio" name="price" value="5.95">');
    $product->assert_equal("", $f->radio_button('price', 2.95),
      '<input id="price" type="radio" name="price" value="2.95">');
  }
}

new Test_ActionView_Helper_FormHelper();

?>
