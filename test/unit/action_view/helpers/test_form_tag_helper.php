<?php

$location = dirname(__FILE__).'/../../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/ActionView/Helpers/TagHelper.php";
require_once MISAGO."/lib/ActionView/Helpers/FormTagHelper.php";

class Test_ActionView_Helpers_FormTagHelper extends Unit_Test
{
  function test_form_tag()
  {
    $this->assert_equal(form_tag('/profiles'), '<form action="/profiles" method="post">');
    $this->assert_equal(form_tag('/profiles', array('method' => 'GET')),
      '<form action="/profiles" method="get">');
    $this->assert_equal(form_tag('/profiles', array('multipart' => true)),
      '<form action="/profiles" method="post" enctype="multipart/form-data">');
    
    $this->assert_equal(form_tag('/profiles', array('method' => 'put')),
      '<form action="/profiles" method="post"><input type="hidden" name="_method" value="put"/>');
    $this->assert_equal(form_tag('/accounts', array('method' => 'delete')),
      '<form action="/accounts" method="post"><input type="hidden" name="_method" value="delete"/>');
    $this->assert_equal(form_tag('/profiles', array('method' => 'put', 'multipart' => true)),
      '<form action="/profiles" method="post" enctype="multipart/form-data"><input type="hidden" name="_method" value="put"/>');
  }
  
  function test_form_tag_with_named_routes()
  {
    $this->assert_equal(form_tag(new ActionController_Path('POST', 'accounts'), array('multipart' => true)),
      '<form action="/accounts" method="post" enctype="multipart/form-data">');
    $this->assert_equal(form_tag(new ActionController_Path('GET', 'accounts')),
      '<form action="/accounts" method="get">');
    $this->assert_equal(form_tag(new ActionController_Path('PUT', 'profiles')),
      '<form action="/profiles" method="post"><input type="hidden" name="_method" value="put"/>');
  }
  
  function test_label()
  {
    $this->assert_equal(label_tag('name'), '<label for="name">Name</label>');
    $this->assert_equal(label_tag('username', 'User name'), '<label for="username">User name</label>');
    $this->assert_equal(label_tag('username', 'User name', array('class' => 'toto')), '<label class="toto" for="username">User name</label>');
    $this->assert_equal(label_tag('username', null, array('class' => 'toto')), '<label class="toto" for="username">Username</label>');
  }
  
  function test_hidden_field()
  {
    $this->assert_equal(hidden_field_tag('name'), '<input type="hidden" id="name" name="name"/>');
    $this->assert_equal(hidden_field_tag('token', 'azerty'), '<input type="hidden" id="token" name="token" value="azerty"/>');
    
    $test = hidden_field_tag('token', 'azerty', array('onchange' => "alert('hidden field changed')"));
    $this->assert_equal($test, '<input onchange="alert(\'hidden field changed\')" type="hidden" id="token" name="token" value="azerty"/>');
  }
  
  function test_text_field()
  {
    $this->assert_equal(text_field_tag('name'), '<input type="text" id="name" name="name"/>');
    $this->assert_equal(text_field_tag('name', 'toto'), '<input type="text" id="name" name="name" value="toto"/>');
    
    $test = text_field_tag('name', null, array('class' => 'special'));
    $this->assert_equal($test, '<input class="special" type="text" id="name" name="name"/>');
    
    $test = text_field_tag('name', '', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal($test, '<input maxlength="15" size="20" type="text" id="name" name="name" value=""/>');
    
    $test = text_field_tag('name', 'some <span>html</span>', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal($test, '<input maxlength="15" size="20" type="text" id="name" name="name" value="some &lt;span&gt;html&lt;/span&gt;"/>');
  }

  function test_text_area()
  {
    $this->assert_equal(text_area_tag('about'), '<textarea id="about" name="about"></textarea>');
    
    $test = text_area_tag('about', null, array('class' => 'small'));
    $this->assert_equal($test, '<textarea class="small" id="about" name="about"></textarea>');
    
    $test = text_area_tag('about', 'some content', array('class' => 'small'));
    $this->assert_equal($test, '<textarea class="small" id="about" name="about">some content</textarea>');
    
    $test = text_area_tag('summary', 'some <html> content', array('class' => 'small'));
    $this->assert_equal($test, '<textarea class="small" id="summary" name="summary">some &lt;html&gt; content</textarea>');
    
    $test = text_area_tag('about', 'some <html> content');
    $this->assert_equal($test, '<textarea id="about" name="about">some &lt;html&gt; content</textarea>');
  }
  
  function test_password_field()
  {
    $this->assert_equal(password_field_tag('name'), '<input type="password" id="name" name="name"/>');
    $this->assert_equal(password_field_tag('name', 'toto'), '<input type="password" id="name" name="name" value="toto"/>');
    
    $test = password_field_tag('name', null, array('class' => 'special'));
    $this->assert_equal($test, '<input class="special" type="password" id="name" name="name"/>');
    
    $test = password_field_tag('name', 'some <span>html</span>', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal($test, '<input maxlength="15" size="20" type="password" id="name" name="name" value="some &lt;span&gt;html&lt;/span&gt;"/>');
  }
  
  function test_file_field()
  {
    $this->assert_equal(file_field_tag('name'), '<input type="file" id="name" name="name"/>');
    
    $test = file_field_tag('name', array('class' => 'special', 'disabled' => true));
    $this->assert_equal($test, '<input class="special" disabled="disabled" type="file" id="name" name="name"/>');
    
    $test = file_field_tag('name', array('accept' => 'image/jpeg,image/png,image/gif'));
    $this->assert_equal($test, '<input accept="image/jpeg,image/png,image/gif" type="file" id="name" name="name"/>');
  }
  
  function test_check_box()
  {
    $this->assert_equal(check_box_tag('receive_email'), '<input type="checkbox" id="receive_email" name="receive_email" value="1"/>');
    
    $test = check_box_tag('receive_email', 'yes');
    $this->assert_equal($test, '<input type="checkbox" id="receive_email" name="receive_email" value="yes"/>');
    
    $test = check_box_tag('publication_status', 1, array('disabled' => true));
    $this->assert_equal($test, '<input disabled="disabled" type="checkbox" id="publication_status" name="publication_status" value="1"/>');
    
    $test = check_box_tag('eula', 42, array('checked' => true));
    $this->assert_equal($test, '<input checked="checked" type="checkbox" id="eula" name="eula" value="42"/>');
    
    $test = check_box_tag('user_agreement', 42, array('disabled' => true, 'checked' => true));
    $this->assert_equal($test, '<input disabled="disabled" checked="checked" type="checkbox" id="user_agreement" name="user_agreement" value="42"/>');
  }

  function test_radio_button()
  {
    $test = radio_button_tag('receive_email', 'yes');
    $this->assert_equal($test, '<input type="radio" id="receive_email" name="receive_email" value="yes"/>');
    
    $test = radio_button_tag('publication_status', 1, array('disabled' => true));
    $this->assert_equal($test, '<input disabled="disabled" type="radio" id="publication_status" name="publication_status" value="1"/>');
    
    $test = radio_button_tag('eula', 42, array('checked' => true));
    $this->assert_equal($test, '<input checked="checked" type="radio" id="eula" name="eula" value="42"/>');
    
    $test = radio_button_tag('user_agreement', 42, array('disabled' => true, 'checked' => true));
    $this->assert_equal($test, '<input disabled="disabled" checked="checked" type="radio" id="user_agreement" name="user_agreement" value="42"/>');
  }

  function test_select()
  {
    $test = select_tag('town');
    $this->assert_equal($test, '<select id="town" name="town"></select>');
    
    $test = select_tag('gender', "<option>male</option><option>female</option>");
    $this->assert_equal($test, '<select id="gender" name="gender"><option>male</option><option>female</option></select>');

    $test = select_tag('town', null, array('multiple' => true));
    $this->assert_equal($test, '<select multiple="multiple" id="town" name="town"></select>');

    $test = select_tag('town', null, array('multiple' => false));
    $this->assert_equal($test, '<select id="town" name="town"></select>');

    $test = select_tag('town', '<option value="">select a town</option>', array('multiple' => true, 'class' => 'towns'));
    $this->assert_equal($test, '<select multiple="multiple" class="towns" id="town" name="town"><option value="">select a town</option></select>');
  }
  
  function test_submit()
  {
    $test = submit_tag();
    $this->assert_equal($test, '<input type="submit"/>');
    
    $test = submit_tag('Create');
    $this->assert_equal($test, '<input type="submit" value="Create"/>');
    
    $test = submit_tag('Create', array('disabled' => true));
    $this->assert_equal($test, '<input disabled="disabled" type="submit" value="Create"/>');
    
    $test = submit_tag('Create', 'edit', array('disabled' => true));
    $this->assert_equal($test, '<input disabled="disabled" name="edit" type="submit" value="Create"/>');
  }
  
  function test_autofocus()
  {
    $test = text_field_tag('user_name', '', array('disabled' => true, 'autofocus' => true, 'required' => false));
    $this->assert_equal($test, '<input disabled="disabled" autofocus="autofocus" type="text" id="user_name" name="user_name" value=""/>');
    
    $test = text_field_tag('user_name', '', array('autofocus' => false, 'required' => true));
    $this->assert_equal($test, '<input required="required" type="text" id="user_name" name="user_name" value=""/>');
    
    $test = text_area_tag('about', '', array('autofocus' => true, 'required' => true));
    $this->assert_equal($test, '<textarea autofocus="autofocus" required="required" id="about" name="about"></textarea>');
    
    $test = select_tag('category', '', array('autofocus' => true, 'required' => false));
    $this->assert_equal($test, '<select autofocus="autofocus" id="category" name="category"></select>');
  }
}

new Test_ActionView_Helpers_FormTagHelper();

?>
