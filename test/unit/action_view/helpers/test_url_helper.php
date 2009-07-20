<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/tag_helper.php";
require_once MISAGO."/lib/action_view/helpers/url_helper.php";

class Test_ActionView_Helpers_UrlHelper extends Unit_Test
{
  function test_link_to()
  {
    $this->assert_equal("", link_to('abcd', '/'), '<a href="/">abcd</a>');
    $this->assert_equal("", link_to('azerty', '/page/123'), '<a href="/page/123">azerty</a>');
    $this->assert_equal("", link_to('azerty', '/page/123', array('class' => 'toto')), '<a class="toto" href="/page/123">azerty</a>');
    $this->assert_equal("", link_to('azerty', '/posts/tag/abcd', array('rel' => 'tag')), '<a rel="tag" href="/posts/tag/abcd">azerty</a>');
    
    $html_link = link_to('delete me', new ActionController_Path('DELETE', 'page/123'));
    $this->assert_equal("", $html_link, '<a class="request_method:delete" href="/page/123">delete me</a>');
    
    $html_link = link_to('destroy me', new ActionController_Path('DELETE', 'page/123'), array('class' => 'destroy'));
    $this->assert_equal("", $html_link, '<a class="destroy request_method:delete" href="/page/123">destroy me</a>');
  }
  
  function test_current_page()
  {
    $_SERVER['REQUEST_URI'] = '/archives';
    $this->assert_true('',  current_page('/archives'));
    $this->assert_false('', current_page('/articles'));
    $this->assert_true('',  current_page(array(':controller' => 'archives')));
    $this->assert_false('', current_page(array(':controller' => 'accounts')));
    $this->assert_false('', current_page(array(':controller' => 'archives', ':action' => 'categories')));
    
    $_SERVER['REQUEST_URI'] = '/articles/show/45';
    $this->assert_false('with params: failure', current_page(array(':controller' => 'archives', ':action' => 'show', ':id' => 45)));
    $this->assert_true('with params: success',  current_page(array(':controller' => 'articles', ':action' => 'show', ':id' => 45)));
    
    $_SERVER['REQUEST_URI'] = '/articles?order=desc';
    $this->assert_true('true even without GET params', current_page(array(':controller' => 'articles')));
    $this->assert_false('wrong GET param', current_page(array(':controller' => 'articles', 'order' => 'asc')));
    $this->assert_true('good GET params',  current_page(array(':controller' => 'articles', 'order' => 'desc')));
  }
  
  function test_link_to_unless_current()
  {
    
  }
}

new Test_ActionView_Helpers_UrlHelper();

?>
