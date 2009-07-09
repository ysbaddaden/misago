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
}

new Test_ActionView_Helpers_UrlHelper();

?>
