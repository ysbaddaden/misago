<?php
require_once __DIR__.'/../../../unit.php';
require_once MISAGO."/lib/Misago/ActionView/Helpers/TagHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/UrlHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/TextHelper.php";

class TestTextHelper extends Misago\Unit\Test
{
  function test_auto_link()
  {
    $str = auto_link("Here is my new website: http://www.bd-en-ligne.fr/ and please email me at me@webcomics.fr");
    $this->assert_equal($str, 'Here is my new website: <a href="http://www.bd-en-ligne.fr/">http://www.bd-en-ligne.fr/</a> '.
      'and please email me at <a href="mailto:me@webcomics.fr">me@webcomics.fr</a>');

    $str = auto_link("Here is my new website: http://www.bd-en-ligne.fr/ and please email me at me@webcomics.fr", 'urls');
    $this->assert_equal($str, 'Here is my new website: <a href="http://www.bd-en-ligne.fr/">http://www.bd-en-ligne.fr/</a> '.
      'and please email me at me@webcomics.fr');

    $str = auto_link("Here is my new website: http://www.bd-en-ligne.fr/ and please email me at me@webcomics.fr", 'email_addresses');
    $this->assert_equal($str, 'Here is my new website: http://www.bd-en-ligne.fr/ '.
      'and please email me at <a href="mailto:me@webcomics.fr">me@webcomics.fr</a>');
    
    $str = auto_link("Here is my new website: http://www.bd-en-ligne.fr/ and please email me at me@webcomics.fr",
      'all', array('class' => 'external'));
    $this->assert_equal($str, 'Here is my new website: <a class="external" href="http://www.bd-en-ligne.fr/">http://www.bd-en-ligne.fr/</a> '.
      'and please email me at <a class="external" href="mailto:me@webcomics.fr">me@webcomics.fr</a>');
  }
  
  function test_highlight()
  {
    $this->assert_equal(highlight("You searched for: misago.", 'misago'), "You searched for: <mark>misago</mark>.");
    $this->assert_equal(highlight("You searched for: misago.", array('misago', 'for')), "You searched <mark>for</mark>: <mark>misago</mark>.");
    $this->assert_equal(highlight("You searched for: misago.", 'misago', '<strong class="highlighter">\1</strong>'), 'You searched for: <strong class="highlighter">misago</strong>.');
  }
  
  function test_excerpt()
  {
    $this->assert_null(excerpt('this is an example', 'not found', 5));
    $this->assert_equal(excerpt('this is an example', 'an', 5), '...s is an exam...');
    $this->assert_equal(excerpt('this is an example', 'is', 5), 'this is a...');
    $this->assert_equal(excerpt('this is an example', 'is'), 'this is an example');
    $this->assert_equal(excerpt('this is an example', 'is', 5, '::'), 'this is a::');
  }
  
  function test_pluralize()
  {
    $this->assert_equal(pluralize(1, 'post'), 'post');
    $this->assert_equal(pluralize(0, 'post'), 'posts');
    $this->assert_equal(pluralize(2, 'post'), 'posts');
    $this->assert_equal(pluralize(2, 'person', 'users'), 'users');
  }
  
  function test_simple_format()
  {
    $this->assert_equal(simple_format("some\nparagraph"), "<p>some<br/>paragraph</p>");
    
    $this->assert_equal(simple_format("some paragraph\n\nsome other\nparagraph"),
      "<p>some paragraph</p><p>some other<br/>paragraph</p>");
    
    $this->assert_equal(simple_format("some paragraph\n\n\nsome other\nparagraph"),
      "<p>some paragraph</p><p>some other<br/>paragraph</p>", 'with many linefeeds');
    
    $this->assert_equal(simple_format("   some\nparagraph   "),
      "<p>some<br/>paragraph</p>", 'with trimming');
  }
  
  function test_truncate()
  {
    $this->assert_equal(truncate(str_repeat('a', 40)), str_repeat('a', 27).'...');
    $this->assert_equal(truncate(str_repeat('z', 40), 20, '<chop>'), str_repeat('z', 14).'<chop>');
    $this->assert_equal(truncate(str_repeat('j', 20)), str_repeat('j', 20));
  }
}

?>
