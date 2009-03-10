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
    $this->assert_equal('', form::hidden_field('token', 'azerty', array('onchange' => "alert('hidden field changed')")), '<input onchange="alert(\'hidden field changed\')" type="hidden" id="token" name="token" value="azerty"/>');
  }
  
  function test_text_field()
  {
    $this->assert_equal('', form::text_field('name'), '<input type="text" id="name" name="name"/>');
    $this->assert_equal('', form::text_field('name', 'toto'), '<input type="text" id="name" name="name" value="toto"/>');
    $this->assert_equal('', form::text_field('name', null, array('class' => 'special')), '<input class="special" type="text" id="name" name="name"/>');
  }
}

new Test_ActionView_Helper_Form();

?>
