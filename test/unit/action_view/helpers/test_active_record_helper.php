<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../../test/test_app/config/boot.php';
require_once MISAGO."/lib/Misago/ActionView/Helpers/TagHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/FormTagHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/ActiveRecordHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/FormHelper.php";

class Test_ActionView_Helpers_ActiveRecordHelper extends Misago\Unit\TestCase
{
  function test_fields_for()
  {
    $product = new Product();
    
    $this->assert_instance_of(fields_for('Product'), 'Misago\ActionView\Helpers\FormHelper\FormBuilder', "using a camelized string");
    $this->assert_instance_of(fields_for('basket'), 'Misago\ActionView\Helpers\FormHelper\FormBuilder',  "using an underscored string");
    $this->assert_instance_of(fields_for($product), 'Misago\ActionView\Helpers\FormHelper\FormBuilder',  "using an ActiveRecord");
  }
  
  function test_label()
  {
    $f = fields_for('Product');
    $this->assert_equal($f->label('available'), '<label for="product_available">Available</label>');
    $this->assert_equal($f->label('title'), '<label for="product_title">Title</label>');
    
    $this->assert_equal($f->label('username', 'User name'), '<label for="product_username">User name</label>');
    $this->assert_equal($f->label('username', 'User name', array('class' => 'username')), '<label class="username" for="product_username">User name</label>');
    $this->assert_equal($f->label('username', null, array('class' => 'username')), '<label class="username" for="product_username">Username</label>');
    $this->assert_equal($f->label('username', array('class' => 'username')), '<label class="username" for="product_username">Username</label>');
    
    $this->assert_equal($f->label('price'), '<label for="product_price">Price of product</label>');
  }
  
  function test_hidden_field()
  {
    $product = new Product(array('price' => 8.97));
    
    $f = fields_for($product);
    $this->assert_equal($f->hidden_field('price'), '<input id="product_price" type="hidden" name="product[price]" value="8.97"/>');
    $this->assert_equal($f->hidden_field('price', array('class' => 'abcd')), '<input class="abcd" id="product_price" type="hidden" name="product[price]" value="8.97"/>');
  }
  
  function test_text_field()
  {
    $product = new Product(array('name' => 'azerty'));
    $f = fields_for($product);
    $this->assert_equal($f->text_field('name'),
      '<input id="product_name" type="text" name="product[name]" value="azerty"/>'
    );
    
    $product = new Product(array('name' => 'bepo'));
    $f = fields_for($product);
    $this->assert_equal($f->text_field('name', array('class' => 'text')),
      '<input class="text" id="product_name" type="text" name="product[name]" value="bepo"/>'
    );
  }
  
  function test_text_area()
  {
    $product = new Product(array('description' => 'some text'));
    $f = fields_for($product);
    $this->assert_equal($f->text_area('description'),
      '<textarea id="product_description" name="product[description]">some text</textarea>'
    );
    
    $product->description = 'some <html> content';
    $this->assert_equal($f->text_area('description', array('class' => 'text')),
      '<textarea class="text" id="product_description" name="product[description]">some &lt;html&gt; content</textarea>'
    );
  }
  
  function test_password_field()
  {
    $product = new Product(array('name' => 'azerty'));
    $f = fields_for($product);
    $this->assert_equal($f->password_field('name'),
      '<input id="product_name" type="password" name="product[name]" value=""/>'
    );
    
    $product = new Product(array('name' => 'bepo'));
    $f = fields_for($product);
    $this->assert_equal($f->password_field('name', array('class' => 'text')),
      '<input class="text" id="product_name" type="password" name="product[name]" value=""/>'
    );
  }
  
  function test_check_box()
  {
    $f = fields_for('Product');
    $this->assert_equal($f->check_box('available'),
      '<input type="hidden" name="product[available]" value="0"/>'.
      '<input id="product_available" type="checkbox" name="product[available]" value="1"/>'
    );
    
    $this->assert_equal($f->check_box('available', array('class' => 'test')),
      '<input type="hidden" name="product[available]" value="0"/>'.
      '<input class="test" id="product_available" type="checkbox" name="product[available]" value="1"/>'
    );
    
    $product = new Product(array('in_stock' => true));
    $f = fields_for($product);
    $this->assert_equal($f->check_box('in_stock'),
      '<input type="hidden" name="product[in_stock]" value="0"/>'.
      '<input id="product_in_stock" checked="checked" type="checkbox" name="product[in_stock]" value="1"/>'
    );
    
    $product = new Product(array('in_stock' => false));
    $f = fields_for($product);
    $this->assert_equal($f->check_box('in_stock', array('class' => 'checkbox')),
      '<input type="hidden" name="product[in_stock]" value="0"/>'.
      '<input class="checkbox" id="product_in_stock" type="checkbox" name="product[in_stock]" value="1"/>'
    );
  }
  
  function test_radio_button()
  {
    $product = new Product(array('category' => 'none'));
    
    $f = fields_for($product);
    $this->assert_equal($f->radio_button('category', 'none'),
      '<input id="product_category_none" checked="checked" type="radio" name="product[category]" value="none"/>');
    $this->assert_equal($f->radio_button('category', 'keyboard'),
      '<input id="product_category_keyboard" type="radio" name="product[category]" value="keyboard"/>');
    $this->assert_equal($f->radio_button('category', 'cover'),
      '<input id="product_category_cover" type="radio" name="product[category]" value="cover"/>');
  }
  
  function test_select()
  {
    $product = new Product(array('category' => 'none'));
    
    $f = fields_for($product);
    $this->assert_equal($f->select('category', array()),
      '<select id="product_category" name="product[category]"></select>');
    
    $options = array(array('yes', 1));
    $this->assert_equal($f->select('in_stock', $options),
      '<select id="product_in_stock" name="product[in_stock]"><option value="1">yes</option></select>');
    
    $options = array(array('yes', 1), array('no', 0));
    $this->assert_equal($f->select('in_stock', $options),
      '<select id="product_in_stock" name="product[in_stock]"><option value="1">yes</option><option value="0">no</option></select>');
  }
  
  function test_index()
  {
    $f = fields_for('Product', array('index' => 2));
    
    $this->assert_equal($f->label('available'), '<label for="product_2_available">Available</label>');
    $this->assert_equal($f->hidden_field('price'),
      '<input id="product_2_price" type="hidden" name="product[2][price]"/>');
    $this->assert_equal($f->text_field('name'),
      '<input id="product_2_name" type="text" name="product[2][name]"/>'
    );
    $this->assert_equal($f->text_area('description'),
      '<textarea id="product_2_description" name="product[2][description]"></textarea>'
    );
    $this->assert_equal($f->password_field('name'),
      '<input id="product_2_name" type="password" name="product[2][name]" value=""/>'
    );
    $this->assert_equal($f->check_box('in_stock'),
      '<input type="hidden" name="product[2][in_stock]" value="0"/>'.
      '<input id="product_2_in_stock" type="checkbox" name="product[2][in_stock]" value="1"/>'
    );
    $this->assert_equal($f->radio_button('category', 'none'),
      '<input id="product_2_category_none" type="radio" name="product[2][category]" value="none"/>');
  }
}

new Test_ActionView_Helpers_ActiveRecordHelper();

?>
