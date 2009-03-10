<?php

class html
{
  /**
   * <code>
   * # inline tags:
   * html::tag('hr')
   * html::tag('a', array('href' => 'http://www.toto.com/'))
   * 
   * # content tags:
   * html::tag('article', $content)
   * html::tag('div', $content, array('class' => 'article'))
   * </code>
   */
  static function tag($name, $content=null, $attributes=null)
  {
    if (is_array($content))
    {
      $attributes = $content;
      $content    = null;
    }
    $attributes = html::parse_attributes($attributes);
    
    if ($content === null) {
      return "<$name$attributes/>";
    }
    return "<$name$attributes>$content</$name>";
  }
  
  static function cdata($content)
  {
    return "<![CDATA[$content]]>";
  }
  
  static function link_to($content, $url, $attributes=null)
  {
    $attributes['href'] = $url;
    return html::tag('a', $content, $attributes);
  }
  
  static function url_for($mapping)
  {
    $map = ActionController_Routing::draw();
    return $map->reverse($mapping);
  }
  
  protected static function parse_attributes($attributes)
  {
    if (empty($attributes)) {
      return '';
    }
    $_attributes = array();
    foreach($attributes as $key => $value)
    {
      $value = str_replace('"', '\"', $value);
      $_attributes[] = "$key=\"$value\"";
    }
    return ' '.implode(' ', $_attributes);
  }
}

?>
