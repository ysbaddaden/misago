<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../../test/test_app/config/boot.php';
require_once MISAGO."/lib/action_view/helpers/tag_helper.php";
require_once MISAGO."/lib/action_view/helpers/url_helper.php";
require_once MISAGO."/lib/action_view/helpers/text_helper.php";

class TestTextHelper extends Unit_Test
{
  function test_auto_link()
  {
    $str = auto_link("Here is my new website: http://www.bd-en-ligne.fr/ and please email me at me@webcomics.fr");
    $this->assert_equal('', $str, 'Here is my new website: <a href="http://www.bd-en-ligne.fr/">http://www.bd-en-ligne.fr/</a> '.
      'and please email me at <a href="mailto:me@webcomics.fr">me@webcomics.fr</a>');

    $str = auto_link("Here is my new website: http://www.bd-en-ligne.fr/ and please email me at me@webcomics.fr", 'urls');
    $this->assert_equal('', $str, 'Here is my new website: <a href="http://www.bd-en-ligne.fr/">http://www.bd-en-ligne.fr/</a> '.
      'and please email me at me@webcomics.fr');

    $str = auto_link("Here is my new website: http://www.bd-en-ligne.fr/ and please email me at me@webcomics.fr", 'email_addresses');
    $this->assert_equal('', $str, 'Here is my new website: http://www.bd-en-ligne.fr/ '.
      'and please email me at <a href="mailto:me@webcomics.fr">me@webcomics.fr</a>');
  }
  
  function test_highlight()
  {
    $this->assert_equal('single phrase', highlight("You searched for: misago.", 'misago'), "You searched for: <mark>misago</mark>.");
    $this->assert_equal('multiple phrases', highlight("You searched for: misago.", array('misago', 'for')), "You searched <mark>for</mark>: <mark>misago</mark>.");
    $this->assert_equal('with particular highlighter', highlight("You searched for: misago.", 'misago', '<strong class="highlighter">\1</strong>'), 'You searched for: <strong class="highlighter">misago</strong>.');
  }
  
  function test_excerpt()
  {
    
  }
  
  function test_markdown()
  {
    
  }
  
  function test_textilizer()
  {
    
  }
  
  function test_pluralize()
  {
    $this->assert_equal('', pluralize(1, 'post'), 'post');
    $this->assert_equal('', pluralize(0, 'post'), 'posts');
    $this->assert_equal('', pluralize(2, 'post'), 'posts');
    $this->assert_equal('', pluralize(2, 'person', 'users'), 'users');
  }
  
  function test_simple_format()
  {
    $this->assert_equal('single paragraph with linebreak', simple_format("some\nparagraph"),
      "<p>some<br/>paragraph</p>");
    
    $this->assert_equal('two paragraphs', simple_format("some paragraph\n\nsome other\nparagraph"),
      "<p>some paragraph</p><p>some other<br/>paragraph</p>");
    
    $this->assert_equal('more than two linefeeds', simple_format("some paragraph\n\n\nsome other\nparagraph"),
      "<p>some paragraph</p><p>some other<br/>paragraph</p>");
    
    $this->assert_equal('trim', simple_format("   some\nparagraph   "),
      "<p>some<br/>paragraph</p>");
  }
  
  function test_truncate()
  {
    $this->assert_equal('', truncate(str_repeat('a', 40)), str_repeat('a', 27).'...');
    $this->assert_equal('', truncate(str_repeat('z', 40), 20, '<chop>'), str_repeat('z', 14).'<chop>');
    $this->assert_equal('', truncate(str_repeat('j', 20)), str_repeat('j', 20));
  }
}
new TestTextHelper();

?>
