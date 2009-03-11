<?php

class form
{
  # TODO: Simulate PUT, DELETE HTTP methods (ie. anything but GET/POST).
  static function form_tag($url, $options=null)
  {
    $method  = isset($options['method']) ? strtolower($options['method']) : 'post';
    $enctype = (isset($options['multipart']) and $options['multipart']) ? ' enctype="multipart/form-data"' : '';
    return "<form action=\"$url\" method=\"$method\"$enctype>";
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
    $attributes = form::input_attributes($name, 'hidden', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function text_field($name, $value=null, $attributes=null)
  {
    $attributes = form::input_attributes($name, 'text', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function text_area($name, $content=null, $attributes=null)
  {
    $attributes = form::input_attributes($name, null, null, $attributes);
    $content    = htmlspecialchars($content);
    return html::tag('textarea', $content, $attributes);
  }
  
  static function password_field($name, $value=null, $attributes=null)
  {
    $attributes = form::input_attributes($name, 'password', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function file_field($name, $attributes=null)
  {
    $attributes = form::input_attributes($name, 'file', null, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function check_box($name, $value=1, $attributes=null)
  {
    $attributes = form::input_attributes($name, 'checkbox', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function radio_button($name, $value, $attributes=null)
  {
    $attributes = form::input_attributes($name, 'radio', $value, $attributes);
    return html::tag('input', $attributes);
  }
  
  static function select()
  {
    
  }
  
  static function submit()
  {
    
  }
  
  private static function input_attributes($name, $type, $value, $attributes)
  {
    if ($type !== null) {
      $attributes['type']  = $type;
    }
    $attributes['id']    = $name;
    $attributes['name']  = $name;
    
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
}

?>
