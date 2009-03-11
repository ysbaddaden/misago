<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/html_tag.php";

class Test_ActionView_Helper_HtmlTag extends Unit_Test
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
  }
}

new Test_ActionView_Helper_HtmlTag();

?>
