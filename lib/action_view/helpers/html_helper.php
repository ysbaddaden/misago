<?php
/*
# A collection of helpful functions to render HTML content.
# 
# DEPRECATED
class ActionView_Helpers_HtmlHelper_Klass
{
  # Parses attributes for form input fields. Creates `id` from `name`;
  # quotes the `value` attribute; parses the `disabled` and `checked`
  # options, etc.
  # 
  # @private
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
  # @private
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

# Renders an HTML tag.
# 
# Inline tags:
# 
#   tag('hr')
#   tag('a', array('href' => 'http://www.toto.com/'))
# 
# Content tags:
# 
#   tag('article', $content)
#   tag('div', $content, array('class' => 'article'))
# 
# @namespace ActionView_Helpers_HtmlHelper
function tag($name, $content=null, $attributes=null)
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
  
  $attributes = ActionView_Helpers_HtmlHelper_Klass::parse_attributes($attributes);
  
  if ($inline_tag) {
    return "<$name$attributes/>";
  }
  return "<$name$attributes>$content</$name>";
}

# Renders a CDATA section.
# 
# @namespace ActionView_Helpers_HtmlHelper
function cdata_section($content)
{
  return "<![CDATA[$content]]>";
}

# Creates a link.
# 
# @namespace ActionView_Helpers_HtmlHelper
function link_to($content, $url, $attributes=null)
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
  return tag('a', $content, $attributes);
}

# Returns the URL for a given mapping.
# 
# @namespace ActionView_Helpers_HtmlHelper
function url_for($mapping)
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
# 
# @namespace ActionView_Helpers_HtmlHelper
function form_tag($url, $attributes=null)
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
  $attributes = ActionView_Helpers_HtmlHelper_Klass::parse_attributes($attributes);
  
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
# 
# @namespace ActionView_Helpers_HtmlHelper
function label_tag($name, $text=null, $attributes=null)
{
  if (empty($text)) {
    $text = String::humanize($name);
  }
  if (!isset($attributes['for'])) {
    $attributes['for'] = $name;
  }
  return tag('label', $text, $attributes);
}

# Renders a hidden form field.
# 
# @namespace ActionView_Helpers_HtmlHelper
function hidden_field_tag($name, $value=null, $attributes=null)
{
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes($name, 'hidden', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a text form field.
# 
# @namespace ActionView_Helpers_HtmlHelper
function text_field_tag($name, $value=null, $attributes=null)
{
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes($name, 'text', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a text form area.
# 
# @namespace ActionView_Helpers_HtmlHelper
function text_area_tag($name, $content=null, $attributes=null)
{
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes($name, null, null, $attributes);
  $content    = htmlspecialchars($content);
  return tag('textarea', $content, $attributes);
}

# Renders a password form field.
# 
# @namespace ActionView_Helpers_HtmlHelper
function password_field_tag($name, $value=null, $attributes=null)
{
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes($name, 'password', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a file upload form field.
# 
# @namespace ActionView_Helpers_HtmlHelper
function file_field_tag($name, $attributes=null)
{
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes($name, 'file', null, $attributes);
  return tag('input', $attributes);
}

# Renders a check box.
# 
# @namespace ActionView_Helpers_HtmlHelper
function check_box_tag($name, $value=1, $attributes=null)
{
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes($name, 'checkbox', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a radio button.
# 
# @namespace ActionView_Helpers_HtmlHelper
function radio_button_tag($name, $value, $attributes=null)
{
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes($name, 'radio', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a select option field.
# 
# @namespace ActionView_Helpers_HtmlHelper
function select_tag($name, $options=null, $attributes=null)
{
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes($name, null, null, $attributes);
  if (isset($attributes['multiple']))
  {
    if ($attributes['multiple']) {
      $attributes['multiple'] = "multiple";
    }
    else {
      unset($attributes['multiple']);
    }
  }
  return tag('select', $options, $attributes);
}

# Renders a submit button.
# 
# @namespace ActionView_Helpers_HtmlHelper
function submit_tag($value=null, $name=null, $attributes=null)
{
  if ($attributes === null and is_array($name))
  {
    $attributes = $name;
    $name = null;
  }
  if ($name !== null) {
    $attributes['name'] = $name;
  }
  $attributes = ActionView_Helpers_HtmlHelper_Klass::input_attributes(null, 'submit', $value, $attributes);
  return tag('input', $attributes);
}
*/
?>
