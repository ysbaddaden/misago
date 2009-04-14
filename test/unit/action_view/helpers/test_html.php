<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/html.php";

class Test_ActionView_Helper_Html extends Unit_Test
{
  function test_cdata()
  {
    $this->assert_equal("", html::cdata("a"), "<![CDATA[a]]>");
    $this->assert_equal("", html::cdata("aroidfkjdf"), "<![CDATA[aroidfkjdf]]>");
  }
  
  function test_tag()
  {
    $this->assert_equal("", html::tag("hr"), "<hr/>");
    $this->assert_equal("", html::tag("br"), "<br/>");
    $this->assert_equal("", html::tag("div", ''), "<div></div>");
    $this->assert_equal("", html::tag("div", 'abcd'), "<div>abcd</div>");

    $this->assert_equal("", html::tag("br", array('class' => 'toto')), "<br class=\"toto\"/>");
    $this->assert_equal("", html::tag("div", 'azerty', array('class' => 'toto')), "<div class=\"toto\">azerty</div>");
    $this->assert_equal("", html::tag("span", null, array('class' => 'abcd')), "<span class=\"abcd\"></span>");
  }
  
  function test_link_to()
  {
    $this->assert_equal("", html::link_to('abcd', '/'), '<a href="/">abcd</a>');
    $this->assert_equal("", html::link_to('azerty', '/page/123'), '<a href="/page/123">azerty</a>');
    $this->assert_equal("", html::link_to('azerty', '/page/123', array('class' => 'toto')), '<a class="toto" href="/page/123">azerty</a>');
    $this->assert_equal("", html::link_to('azerty', '/posts/tag/abcd', array('rel' => 'tag')), '<a rel="tag" href="/posts/tag/abcd">azerty</a>');
    
    $html_link = html::link_to('delete me', new ActionController_Path('DELETE', 'page/123'));
    $this->assert_equal("", $html_link, '<a class="request_method:delete" href="/page/123">delete me</a>');
    
    $html_link = html::link_to('delete me', new ActionController_Path('DELETE', 'page/123'), array('class' => 'delete'));
    $this->assert_equal("", $html_link, '<a class="delete request_method:delete" href="/page/123">delete me</a>');
  }
  
  
  function test_form_tag()
  {
    $this->assert_equal('', html::form_tag('/profiles'), '<form action="/profiles" method="post">');
    $this->assert_equal('', html::form_tag('/profiles', array('method' => 'GET')),
      '<form action="/profiles" method="get">');
    $this->assert_equal('', html::form_tag('/profiles', array('multipart' => true)),
      '<form action="/profiles" method="post" enctype="multipart/form-data">');
    
    $this->assert_equal('', html::form_tag('/profiles', array('method' => 'put')),
      '<form action="/profiles" method="post"><input type="hidden" name="_method" value="put"/>');
    $this->assert_equal('', html::form_tag('/accounts', array('method' => 'delete')),
      '<form action="/accounts" method="post"><input type="hidden" name="_method" value="delete"/>');
    $this->assert_equal('', html::form_tag('/profiles', array('method' => 'put', 'multipart' => true)),
      '<form action="/profiles" method="post" enctype="multipart/form-data"><input type="hidden" name="_method" value="put"/>');

    $this->assert_equal('', html::form_tag(new ActionController_Path('POST', 'accounts'), array('multipart' => true)),
      '<form action="/accounts" method="post" enctype="multipart/form-data">');
    $this->assert_equal('', html::form_tag(new ActionController_Path('GET', 'accounts')),
      '<form action="/accounts" method="get">');
    $this->assert_equal('', html::form_tag(new ActionController_Path('PUT', 'profiles')),
      '<form action="/profiles" method="post"><input type="hidden" name="_method" value="put"/>');
  }
  
  function test_label()
  {
    $this->assert_equal('', html::label('name'), '<label for="name">Name</label>');
    $this->assert_equal('', html::label('username', 'User name'), '<label for="username">User name</label>');
    $this->assert_equal('', html::label('username', 'User name', array('class' => 'toto')), '<label class="toto" for="username">User name</label>');
    $this->assert_equal('', html::label('username', null, array('class' => 'toto')), '<label class="toto" for="username">Username</label>');
  }
  
  function test_hidden_field()
  {
    $this->assert_equal('', html::hidden_field('name'), '<input type="hidden" id="name" name="name"/>');
    $this->assert_equal('', html::hidden_field('token', 'azerty'), '<input type="hidden" id="token" name="token" value="azerty"/>');
    
    $test = html::hidden_field('token', 'azerty', array('onchange' => "alert('hidden field changed')"));
    $this->assert_equal('', $test, '<input onchange="alert(\'hidden field changed\')" type="hidden" id="token" name="token" value="azerty"/>');
  }
  
  function test_text_field()
  {
    $this->assert_equal('', html::text_field('name'), '<input type="text" id="name" name="name"/>');
    $this->assert_equal('', html::text_field('name', 'toto'), '<input type="text" id="name" name="name" value="toto"/>');
    
    $test = html::text_field('name', null, array('class' => 'special'));
    $this->assert_equal('', $test, '<input class="special" type="text" id="name" name="name"/>');
    
    $test = html::text_field('name', '', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal('', $test, '<input maxlength="15" size="20" type="text" id="name" name="name" value=""/>');
    
    $test = html::text_field('name', 'some <span>html</span>', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal('', $test, '<input maxlength="15" size="20" type="text" id="name" name="name" value="some &lt;span&gt;html&lt;/span&gt;"/>');
  }

  function test_text_area()
  {
    $this->assert_equal('', html::text_area('about'), '<textarea id="about" name="about"></textarea>');
    
    $test = html::text_area('about', null, array('class' => 'small'));
    $this->assert_equal('', $test, '<textarea class="small" id="about" name="about"></textarea>');
    
    $test = html::text_area('about', 'some content', array('class' => 'small'));
    $this->assert_equal('', $test, '<textarea class="small" id="about" name="about">some content</textarea>');
    
    $test = html::text_area('summary', 'some <html> content', array('class' => 'small'));
    $this->assert_equal('', $test, '<textarea class="small" id="summary" name="summary">some &lt;html&gt; content</textarea>');
    
    $test = html::text_area('about', 'some <html> content');
    $this->assert_equal('', $test, '<textarea id="about" name="about">some &lt;html&gt; content</textarea>');
  }
  
  function test_password_field()
  {
    $this->assert_equal('', html::password_field('name'), '<input type="password" id="name" name="name"/>');
    $this->assert_equal('', html::password_field('name', 'toto'), '<input type="password" id="name" name="name" value="toto"/>');
    
    $test = html::password_field('name', null, array('class' => 'special'));
    $this->assert_equal('', $test, '<input class="special" type="password" id="name" name="name"/>');
    
    $test = html::password_field('name', 'some <span>html</span>', array('maxlength' => 15, 'size' => 20));
    $this->assert_equal('', $test, '<input maxlength="15" size="20" type="password" id="name" name="name" value="some &lt;span&gt;html&lt;/span&gt;"/>');
  }
  
  function test_file_field()
  {
    $this->assert_equal('', html::file_field('name'), '<input type="file" id="name" name="name"/>');
    
    $test = html::file_field('name', array('class' => 'special', 'disabled' => true));
    $this->assert_equal('', $test, '<input class="special" disabled="disabled" type="file" id="name" name="name"/>');
    
    $test = html::file_field('name', array('accept' => 'image/jpeg,image/png,image/gif'));
    $this->assert_equal('', $test, '<input accept="image/jpeg,image/png,image/gif" type="file" id="name" name="name"/>');
  }
  
  function test_check_box()
  {
    $this->assert_equal('', html::check_box('receive_email'), '<input type="checkbox" id="receive_email" name="receive_email" value="1"/>');
    
    $test = html::check_box('receive_email', 'yes');
    $this->assert_equal('', $test, '<input type="checkbox" id="receive_email" name="receive_email" value="yes"/>');
    
    $test = html::check_box('publication_status', 1, array('disabled' => true));
    $this->assert_equal('', $test, '<input disabled="disabled" type="checkbox" id="publication_status" name="publication_status" value="1"/>');
    
    $test = html::check_box('eula', 42, array('checked' => true));
    $this->assert_equal('', $test, '<input checked="checked" type="checkbox" id="eula" name="eula" value="42"/>');
    
    $test = html::check_box('user_agreement', 42, array('disabled' => true, 'checked' => true));
    $this->assert_equal('', $test, '<input disabled="disabled" checked="checked" type="checkbox" id="user_agreement" name="user_agreement" value="42"/>');
  }

  function test_radio_button()
  {
    $test = html::radio_button('receive_email', 'yes');
    $this->assert_equal('', $test, '<input type="radio" id="receive_email" name="receive_email" value="yes"/>');
    
    $test = html::radio_button('publication_status', 1, array('disabled' => true));
    $this->assert_equal('', $test, '<input disabled="disabled" type="radio" id="publication_status" name="publication_status" value="1"/>');
    
    $test = html::radio_button('eula', 42, array('checked' => true));
    $this->assert_equal('', $test, '<input checked="checked" type="radio" id="eula" name="eula" value="42"/>');
    
    $test = html::radio_button('user_agreement', 42, array('disabled' => true, 'checked' => true));
    $this->assert_equal('', $test, '<input disabled="disabled" checked="checked" type="radio" id="user_agreement" name="user_agreement" value="42"/>');
  }

  function test_select()
  {
    $test = html::select('town');
    $this->assert_equal('', $test, '<select id="town" name="town"></select>');
    
    $test = html::select('gender', "<option>male</option><option>female</option>");
    $this->assert_equal('', $test, '<select id="gender" name="gender"><option>male</option><option>female</option></select>');

    $test = html::select('town', null, array('multiple' => true));
    $this->assert_equal('', $test, '<select multiple="multiple" id="town" name="town"></select>');

    $test = html::select('town', null, array('multiple' => false));
    $this->assert_equal('', $test, '<select id="town" name="town"></select>');

    $test = html::select('town', '<option value="">select a town</option>', array('multiple' => true, 'class' => 'towns'));
    $this->assert_equal('', $test, '<select multiple="multiple" class="towns" id="town" name="town"><option value="">select a town</option></select>');
  }
  
  function test_submit()
  {
    $test = html::submit();
    $this->assert_equal('', $test, '<input type="submit"/>');
    
    $test = html::submit('Create');
    $this->assert_equal('', $test, '<input type="submit" value="Create"/>');
    
    $test = html::submit('Create', array('disabled' => true));
    $this->assert_equal('', $test, '<input disabled="disabled" type="submit" value="Create"/>');
    
    $test = html::submit('Create', 'edit', array('disabled' => true));
    $this->assert_equal('', $test, '<input disabled="disabled" name="edit" type="submit" value="Create"/>');
  }
}

new Test_ActionView_Helper_Html();

?>
