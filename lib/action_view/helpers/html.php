<?php

class html
{
  static function tag($name, $content=null, $attributes=null)
  {
    if (is_array($content))
    {
      $attributes = $content;
      $content    = null;
    }
    $attributes = html::parse_attributes($attributes);
    
    if ($content === null)
    {
      return "<$name$attributes/>";
    }
    return "<$name$attributes>$content</$name>";
  }
  
  static function cdata($content)
  {
    return "<![CDATA[$content]]>";
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
