<?php

# @package ActionView
# @subpackage Helpers
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
      $inline_tag = true;
    }
    else {
      $inline_tag = ($content === null and $attributes === null);
    }
    
    $attributes = html::parse_attributes($attributes);
    
    if ($inline_tag) {
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
  
  static function form_tag($url, $options=null)
  {
    $method  = isset($options['method']) ? strtolower($options['method']) : 'post';
    $enctype = (isset($options['multipart']) and $options['multipart']) ? ' enctype="multipart/form-data"' : '';
    
    if ($method == 'get' or $method == 'post') {
      $str = "<form action=\"$url\" method=\"$method\"$enctype>";
    }
    else
    {
      $str  = "<form action=\"$url\" method=\"post\"$enctype>";
      $str .= '<input type="hidden" name="_method" value="'.$method.'"/>';
    }
    return $str;
  }
  
  static function label($name, $text=null, $attributes=null)
  {
    if (empty($text)) {
      $text = String::humanize($name);
    }
    if (!isset($attributes['for'])) {
      $attributes['for'] = $name;
    }
    return html::tag('label', $text, $attributes);
  }
  
  static function hidden_field($name, $value=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'hidden', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function text_field($name, $value=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'text', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function text_area($name, $content=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, null, null, $attributes);
    $content    = htmlspecialchars($content);
    return html::tag('textarea', $content, $attributes);
  }
  
  static function password_field($name, $value=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'password', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function file_field($name, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'file', null, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function check_box($name, $value=1, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'checkbox', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function radio_button($name, $value, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'radio', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function select($name, $options=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, null, null, $attributes);
    if (isset($attributes['multiple']))
    {
      if ($attributes['multiple']) {
        $attributes['multiple'] = "multiple";
      }
      else {
        unset($attributes['multiple']);
      }
    }
    return html::tag('select', $options, $attributes);
  }
  
  static function submit($value=null, $name=null, $attributes=null)
  {
    if ($attributes === null and is_array($name))
    {
      $attributes = $name;
      $name = null;
    }
    if ($name !== null) {
      $attributes['name'] = $name;
    }
    $attributes = html::input_attributes(null, 'submit', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  
  static function input_attributes($name, $type, $value, $attributes)
  {
    if ($type !== null) {
      $attributes['type']  = $type;
    }
    
    if ($name !== null)
    {
      if (!isset($attributes['id'])) {
        $attributes['id'] = $name;
      }
      $attributes['name'] = $name;
    }
    
    if ($value !== null) {
      $attributes['value'] = htmlspecialchars($value);
    }
    
    if (isset($attributes['disabled']))
    {
      if ($attributes['disabled']) {
        $attributes['disabled'] = "disabled";
      }
      else {
        unset($attributes['disabled']);
      }
    }
    
    if ($type == 'radio' or $type == 'checkbox')
    {
      if (isset($attributes['checked']))
      {
        if ($attributes['checked']) {
          $attributes['checked'] = "checked";
        }
        else {
          unset($attributes['checked']);
        }
      }
    }
    return $attributes;
  }
  
  static function parse_attributes($attributes)
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
