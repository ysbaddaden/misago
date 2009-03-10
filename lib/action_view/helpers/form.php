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
    $attributes['type']  = 'hidden';
    $attributes['id']    = $name;
    $attributes['name']  = $name;
    if ($value !== null) {
      $attributes['value'] = $value;
    }
    return html::tag('input', $attributes);
  }
  
  static function text_field()
  {
    
  }
  
  static function text_area()
  {
    
  }
  
  static function password_field()
  {
    
  }
  
  static function file_field()
  {
    
  }
  
  static function check_box()
  {
    
  }
  
  static function radio_button()
  {
    
  }
  
  static function select()
  {
    
  }
  
  static function submit()
  {
    
  }
}

?>
