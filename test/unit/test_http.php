<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../test_app/config/boot.php";

class Test_HTTP extends Unit_Test
{
  function test_flatten_postfields()
  {
    $postfields = HTTP::flatten_postfields(array('a' => 1, 'b' => 'aze'));
    $this->assert_equal("basic", $postfields, array('a=1', 'b=aze'));
    
    $postfields = HTTP::flatten_postfields(array("catégorie" => '100% naturel'));
    $this->assert_equal("data must be urlencoded", $postfields, array('cat%C3%A9gorie=100%25+naturel'));
    
    $postfields = HTTP::flatten_postfields(array("contact" => array('subject' => 'aaa', 'message' => 'bbb')));
    $this->assert_equal("must be recursive", $postfields, array('contact%5Bsubject%5D=aaa', 'contact%5Bmessage%5D=bbb'));
    
    $postfields = HTTP::flatten_postfields(array("post" => array('title' => 'aaa', 'body' => 'ccc', 'tags' => array('a', 'b', 'c'))));
    $this->assert_equal("must be really recursive", $postfields, array('post%5Btitle%5D=aaa', 'post%5Bbody%5D=ccc',
      'post%5Btags%5D%5B0%5D=a', 'post%5Btags%5D%5B1%5D=b', 'post%5Btags%5D%5B2%5D=c'));
  }
}

new Test_HTTP();

?>
