<?php

# A collection of helpful functions to render HTML content.
# 
# @package ActionView
# @subpackage Helpers
class html
{
  # Renders an HTML tag.
  # 
  # Inline tags:
  # 
  #   html::tag('hr')
  #   html::tag('a', array('href' => 'http://www.toto.com/'))
  # 
  # Content tags:
  # 
  #   html::tag('article', $content)
  #   html::tag('div', $content, array('class' => 'article'))
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
  
  # Creates a link.
  static function link_to($content, $url, $attributes=null)
  {
    if (is_object($url) and isset($url->method))
    {
      $method = strtolower($url->method);
      if ($url->method != 'GET')
      {
        if (isset($attributes['class'])) {
          $attributes['class'] .= ' request_method:'.$method;
        }
        else {
          $attributes['class'] = 'request_method:'.$method;
        }
      }
    }
    $attributes['href'] = $url;
    return html::tag('a', $content, $attributes);
  }
  
  # Returns the URL for a given mapping.
  static function url_for($mapping)
  {
    $map = ActionController_Routing::draw();
    return $map->reverse($mapping);
  }
  
  # Creates a form opening tag.
  # 
  # Options:
  # 
  # - multipart: sets enctype to multipart/form-data;
  # 
  # If method is different of GET or POST, a hidden field
  # will be added: `_method`.
  static function form_tag($url, $attributes=null)
  {
    if (isset($attributes['method']))
    {
      $method = strtolower($attributes['method']);
      unset($attributes['method']);
    }
    elseif (is_object($url) and isset($url->method)) {
      $method = strtolower($url->method);
    }
    else {
      $method = 'post';
    }
    if (isset($attributes['multipart']) and $attributes['multipart'])
    {
      $attributes['enctype'] = "multipart/form-data";
      unset($attributes['multipart']);
    }
    $attributes = html::parse_attributes($attributes);
    
    if ($method == 'get' or $method == 'post') {
      $str = "<form action=\"$url\" method=\"$method\"$attributes>";
    }
    else
    {
      $str  = "<form action=\"$url\" method=\"post\"$attributes>";
      $str .= '<input type="hidden" name="_method" value="'.$method.'"/>';
    }
    return $str;
  }
  
  # Renders a form label.
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
  
  # Renders a hidden form field.
  static function hidden_field($name, $value=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'hidden', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  # Renders a text form field.
  static function text_field($name, $value=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'text', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  # Renders a text form area.
  static function text_area($name, $content=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, null, null, $attributes);
    $content    = htmlspecialchars($content);
    return html::tag('textarea', $content, $attributes);
  }
  
  # Renders a password form field.
  static function password_field($name, $value=null, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'password', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  # Renders a file upload form field.
  static function file_field($name, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'file', null, $attributes);
    return html::tag('input', $attributes);
  }
  
  # Renders a check box.
  static function check_box($name, $value=1, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'checkbox', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  # Renders a radio button.
  static function radio_button($name, $value, $attributes=null)
  {
    $attributes = html::input_attributes($name, 'radio', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  # Renders a select option field.
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
  
  # Renders a submit button.
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
  
  # Parses attributes for form input fields. Creates `id` from `name`;
  # quotes the `value` attribute; parses the `disabled` and `checked`
  # options, etc.
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
  
  # Parses attributes for any HTML tag.
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
