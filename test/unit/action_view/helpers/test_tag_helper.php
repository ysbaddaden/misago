<?php

$location = dirname(__FILE__).'/../../../..';
$_SERVER['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/tag_helper.php";

class Test_ActionView_Helpers_TagHelper extends Unit_Test
{
  function test_cdata()
  {
    $this->assert_equal("", cdata_section("a"), "<![CDATA[a]]>");
    $this->assert_equal("", cdata_section("aroidfkjdf"), "<![CDATA[aroidfkjdf]]>");
  }
  
  function test_tag()
  {
    $this->assert_equal("", tag("hr"), "<hr/>");
    $this->assert_equal("", tag("br"), "<br/>");
    $this->assert_equal("", tag("div", ''), "<div></div>");
    $this->assert_equal("", tag("div", 'abcd'), "<div>abcd</div>");

    $this->assert_equal("", tag("br", array('class' => 'toto')), "<br class=\"toto\"/>");
    $this->assert_equal("", tag("div", 'azerty', array('class' => 'toto')), "<div class=\"toto\">azerty</div>");
    $this->assert_equal("", tag("span", null, array('class' => 'abcd')), "<span class=\"abcd\"></span>");
  }
}

new Test_ActionView_Helpers_TagHelper();

?>
