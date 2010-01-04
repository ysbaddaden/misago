<?php
namespace Misago\Unit\Assertions;

abstract class DomAssertions extends ModelAssertions
{
  # HTML strings must be identical, up to attributes.
  function assert_dom_equal($expected, $actual, $message='')
  {
    $message = $this->build_message($message,
      "expected %s to be DOM == with %s", $actual, $expected);
    $this->assert_block($message, function() use($expected, $actual)
    {
      $expected = \HTML5_Parser::parseFragment($expected);
      $actual   = \HTML5_Parser::parseFragment($actual);
      return $actual == $expected;
    });
  }
  
  # Negated form of <tt>assert_dom_equal</tt>.
  function assert_dom_not_equal($expected, $actual, $message='')
  {
    $message = $this->build_message($message,
      "expected %s to be DOM != with %s", $actual, $expected);
    $this->assert_block($message, function() use($expected, $actual)
    {
      $expected = \HTML5_Parser::parseFragment($expected);
      $actual   = \HTML5_Parser::parseFragment($actual);
      return $actual != $expected;
    });
  }
  
  # Makes tests on DOM using CSS selectors.
  # 
  # Attention: HTML/XML must be well formed.
  # 
  #   assert_select('one or more forms', 'form')
  #   assert_select('no forms', 'form', false)
  #   assert_select('must contain four articles', 'article', 4)
  #   assert_select('2 articles with class foo', 'article.foo', 2)
  #   assert_select('title element must contains "welcome" text', 'head title', 'welcome')
  #
  protected function assert_select($selector, $equality=true, $message='')
  {
    $elements = $this->css_select($this->response['body'], $selector);
    
    switch (gettype($equality))
    {
      case 'boolean':
        switch($equality)
        {
          case true:
            $message = $this->build_message($message, "expected matches for %s but got none", $selector);
            $this->assert_block($message, function() use ($elements) {
              return (count($elements) > 0);
            });
          break;
          
          case false:
            $message = $this->build_message($message, "expected no matches for %s but got some", $selector);
            $this->assert_block($message, function() use ($elements) {
              return (count($elements) == 0);
            });
          break;
        }
      break;
      
      case 'integer':
        $message = $this->build_message($message, "expected %d matches for %s but got %d", $equality, $selector, count($elements));
        $this->assert_block($message, function() use ($elements, $equality) {
          return (count($elements) == $equality);
        });
      break;
      
      case 'string':
        $text = '';
        foreach($elements as $elm) {
          $text .= $elm['text'];
        }
        $this->assert_match('/'.preg_quote($equality).'/', $text, $message);
      break;
    }
  }
  
  # Returns an array of elements matching given selector.
  protected function css_select($html, $selector)
  {
    $s = new DOMSelector($html);
    return $s->select($selector);
  }
}

# CSS selector is derived from SelectorDom Copyright TJ Holowaychuk <tj@vision-media.ca>
# :nodoc:
class DOMSelector
{
  function __construct($html)
  {
#    # drops DOCTYPE since it triggers a warning (StartTag: invalid element name in Entity)
#    $html = preg_replace('/<!DOCTYPE (?:.+?)>/i', '', $html);
#    
#    # drops XMLNS definitions because XPATH evaluates nothing when XMLNS are defined
#    $html = preg_replace('/(<.*)xmlns="(?:.+?)"(.*>)/', '\1\2', $html);
#    
#    $this->dom = new \DOMDocument();
#    $this->dom->loadXML(html_entity_decode($html, ENT_NOQUOTES, 'UTF-8'));
    
    $this->dom   = \HTML5_Parser::parse($html);
    $this->xpath = new \DOMXpath($this->dom);
  }
  
  function select($selector)
  {
    $selector = $this->selector_to_xpath($selector);
    $elements = $this->xpath->evaluate($selector);
    return $this->elements_to_array($elements);
  }
  
  function & elements_to_array($elements)
  {
    $ary = array();
    for ($i = 0, $length = $elements->length; $i < $length; ++$i)
    {
      if ($elements->item($i)->nodeType == XML_ELEMENT_NODE) {
        array_push($ary, $this->element_to_array($elements->item($i)));
      }
    }
    return $ary;
  }
  
  function element_to_array($element)
  {
    $ary = array(
      'name'       => $element->nodeName,
      'attributes' => array(),
      'text'       => $element->textContent,
      'children'   => $this->elements_to_array($element->childNodes)
    );
    foreach((array)$element->attributes as $key => $attr) {
      $ary['attributes'][$key] = $attr->value;
    }
    return $ary;
  }
  
  # http://plasmasturm.org/log/444/
  function selector_to_xpath($selector)
  {
    $selector = 'descendant-or-self::' . $selector;
    
    // :button, :submit, etc
#    $selector = preg_replace('/:(button|submit|file|checkbox|radio|image|reset|text|password)/', 'input[@type="\1"]', $selector);

    // [id]
    $selector = preg_replace('/\[(\w+)\]/', '*[@\1]', $selector);
    
    // foo[id=foo]
    $selector = preg_replace('/\[(\w+)=[\'"]?(.*?)[\'"]?\]/', '[@\1="\2"]', $selector);
    
    // [id=foo]
    $selector = str_replace(':[', ':*[', $selector);
    
    // div#foo
    $selector = preg_replace('/([\w\-]+)\#([\w\-]+)/', '\1[@id="\2"]', $selector);
    
    // #foo
    $selector = preg_replace('/\#([\w\-]+)/', '*[@id="\1"]', $selector);
    
    // div.foo
    $selector = preg_replace('/([\w\-]+)\.([\w\-]+)/', '\1[contains(concat(" ",@class," ")," \2 ")]', $selector);
    
    // .foo
    $selector = preg_replace('/\.([\w\-]+)/', '*[contains(concat(" ",@class," ")," \1 ")]', $selector);
    
    // div:first-child
    $selector = preg_replace('/([\w\-]+):first-child/', '*/\1[position()=1]', $selector);
    
    // div:last-child
    $selector = preg_replace('/([\w\-]+):last-child/', '*/\1[position()=last()]', $selector);
    
    // :first-child
    $selector = str_replace(':first-child', '*/*[position()=1]', $selector);
    
    // :last-child
    $selector = str_replace(':last-child', '*/*[position()=last()]', $selector);
    
    // div:nth-child
    $selector = preg_replace('/([\w\-]+):nth-child\((\d+)\)/', '*/\1[position()=\2]', $selector);
    
    // :nth-child
    $selector = preg_replace('/:nth-child\((\d+)\)/', '*/*[position()=\1]', $selector);
    
    // :contains(Foo)
    $selector = preg_replace('/([\w\-]+):contains\((.*?)\)/', '\1[contains(string(.),"\2")]', $selector);
    
    // >
    $selector = preg_replace('/\s*>\s*/', '/', $selector);
    
    // ~
    $selector = preg_replace('/\s*~\s*/', '/following-sibling::', $selector);
    
    // +
    $selector = preg_replace('/\s*\+\s*([\w\-]+)/', '/following-sibling::\1[position()=1]', $selector);
    
    // ' '
    $selector = preg_replace(
      array('/\s+/', '/"\/descendant::"/', '/"\/descendant::/', '/\/descendant::"/'),
      array('/descendant::', '" "', '" ', ' "'),
      $selector
    );
    $selector = str_replace(']*', ']', $selector);
    $selector = str_replace(']/*', ']', $selector);
    
    return $selector;
  }
}

?>
