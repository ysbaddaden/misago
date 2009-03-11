<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/html.php";
require_once MISAGO."/lib/action_view/helpers/form.php";

class Test_ActionView_Helper_Form extends Unit_Test
{
  function test_form_tag()
  {
    $this->assert_equal('', form::form_tag('/profiles'), '<form action="/profiles" method="post">');
    $this->assert_equal('', form::form_tag('/profiles', array('method' => 'GET')), '<form action="/profiles" method="get">');
    $this->assert_equal('', form::form_tag('/profiles', array('multipart' => true)), '<form action="/profiles" method="post" enctype="multipart/form-data">');
  }
  
  function test_label()
  {
    $this->assert_equal('', form::label('name'), '<label for="name">Name</label>');
    $this->assert_equal('', form::label('username', 'User name'), '<label for="username">User name</label>');
    $this->assert_equal('', form::label('username', 'User name', array('class' => 'toto')), '<label class="toto" for="username">User name</label>');
    $this->assert_equal('', form::label('username', null, array('class' => 'toto')), '<label class="toto" for="username">Username</label>');
  }
  
  function test_hidden_field()
  {
    $this->assert_equal('', form::hidden_field('name'), '<input type="hidden" id="name" name="name"/>');
    $this->assert_equal('', form::hidden_field('token', 'azerty'), '<input type="hidden" id="token" name="token" value="azerty"/>');
    
    $test = form::hidden_field('token', 'azerty', array('onchange' => "alert('hidden field changed')"));
    $this->assert_equal('', $test, '<input onchange="alert(\'hidden field changed\')" type="hidden" id="token" name="token" value="azerty"/>');
  }
  
  function test_text_field()
  {
    $this->assert_equal('', form::text_field('name'), '<input type="text" id="name" name="name"/>');
    $this->assert_equal('', form::text_field('name', 'toto'), '<input type="text" id="name" name="name" value="toto"/>');
    
    $test = form::text_field('name', null, array('class' => 'special'));
    $this->assert_equal('', $test, '<input class="special" type="text" id="name" name="name"/>');
    
    $test = form::text_field('name', '', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal('', $test, '<input maxlength="15" size="20" type="text" id="name" name="name" value=""/>');
    
    $test = form::text_field('name', 'some <span>html</span>', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal('', $test, '<input maxlength="15" size="20" type="text" id="name" name="name" value="some &lt;span&gt;html&lt;/span&gt;"/>');
  }

  function test_text_area()
  {
    $this->assert_equal('', form::text_area('about'), '<textarea id="about" name="about"></textarea>');
    
    $test = form::text_area('about', null, array('class' => 'small'));
    $this->assert_equal('', $test, '<textarea class="small" id="about" name="about"></textarea>');
    
    $test = form::text_area('about', 'some content', array('class' => 'small'));
    $this->assert_equal('', $test, '<textarea class="small" id="about" name="about">some content</textarea>');
    
    $test = form::text_area('summary', 'some <html> content', array('class' => 'small'));
    $this->assert_equal('', $test, '<textarea class="small" id="summary" name="summary">some &lt;html&gt; content</textarea>');
    
    $test = form::text_area('about', 'some <html> content');
    $this->assert_equal('', $test, '<textarea id="about" name="about">some &lt;html&gt; content</textarea>');
  }
  
  function test_password_field()
  {
    $this->assert_equal('', form::password_field('name'), '<input type="password" id="name" name="name"/>');
    $this->assert_equal('', form::password_field('name', 'toto'), '<input type="password" id="name" name="name" value="toto"/>');
    
    $test = form::password_field('name', null, array('class' => 'special'));
    $this->assert_equal('', $test, '<input class="special" type="password" id="name" name="name"/>');
    
    $test = form::password_field('name', 'some <span>html</span>', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal('', $test, '<input maxlength="15" size="20" type="password" id="name" name="name" value="some &lt;span&gt;html&lt;/span&gt;"/>');
  }
  
  function test_file_field()
  {
    $this->assert_equal('', form::file_field('name'), '<input type="file" id="name" name="name"/>');
    
    $test = form::file_field('name', array('class' => 'special', 'disabled' => true));
    $this->assert_equal('', $test, '<input class="special" disabled="disabled" type="file" id="name" name="name"/>');
    
    $test = form::file_field('name', array('accept' => 'image/jpeg,image/png,image/gif'));
    $this->assert_equal('', $test, '<input accept="image/jpeg,image/png,image/gif" type="file" id="name" name="name"/>');
  }
  
  function test_check_box()
  {
    $this->assert_equal('', form::check_box('receive_email'), '<input type="checkbox" id="receive_email" name="receive_email" value="1"/>');
    
    $test = form::check_box('receive_email', 'yes');
    $this->assert_equal('', $test, '<input type="checkbox" id="receive_email" name="receive_email" value="yes"/>');
    
    $test = form::check_box('receive_email', 1, array('disabled' => true));
    $this->assert_equal('', $test, '<input disabled="disabled" type="checkbox" id="receive_email" name="receive_email" value="1"/>');
    
    $test = form::check_box('receive_email', 42, array('checked' => true));
    $this->assert_equal('', $test, '<input checked="checked" type="checkbox" id="receive_email" name="receive_email" value="42"/>');
    
    $test = form::check_box('receive_email', 42, array('disabled' => true, 'checked' => true));
    $this->assert_equal('', $test, '<input disabled="disabled" checked="checked" type="checkbox" id="receive_email" name="receive_email" value="42"/>');
  }
}

new Test_ActionView_Helper_Form();

?>
