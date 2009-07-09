<?php

# Creates a form opening tag.
# 
# Options:
# 
# - multipart: sets enctype to multipart/form-data;
# 
# If method is different of GET or POST, a hidden field
# will be added: `_method`.
# 
# @namespace ActionView_Helpers_FormTagHelper
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
  $attributes = ActionView_Helpers_TagHelper_NS::parse_attributes($attributes);
  
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
# @namespace ActionView_Helpers_FormTagHelper
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
# @namespace ActionView_Helpers_FormTagHelper
function hidden_field_tag($name, $value=null, $attributes=null)
{
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes($name, 'hidden', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a text form field.
# 
# @namespace ActionView_Helpers_FormTagHelper
function text_field_tag($name, $value=null, $attributes=null)
{
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes($name, 'text', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a text form area.
# 
# @namespace ActionView_Helpers_FormTagHelper
function text_area_tag($name, $content=null, $attributes=null)
{
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes($name, null, null, $attributes);
  $content    = htmlspecialchars($content);
  return tag('textarea', $content, $attributes);
}

# Renders a password form field.
# 
# @namespace ActionView_Helpers_FormTagHelper
function password_field_tag($name, $value=null, $attributes=null)
{
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes($name, 'password', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a file upload form field.
# 
# @namespace ActionView_Helpers_FormTagHelper
function file_field_tag($name, $attributes=null)
{
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes($name, 'file', null, $attributes);
  return tag('input', $attributes);
}

# Renders a check box.
# 
# @namespace ActionView_Helpers_FormTagHelper
function check_box_tag($name, $value=1, $attributes=null)
{
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes($name, 'checkbox', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a radio button.
# 
# @namespace ActionView_Helpers_FormTagHelper
function radio_button_tag($name, $value, $attributes=null)
{
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes($name, 'radio', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a select option field.
# 
# @namespace ActionView_Helpers_FormTagHelper
function select_tag($name, $options=null, $attributes=null)
{
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes($name, null, null, $attributes);
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
# @namespace ActionView_Helpers_FormTagHelper
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
  $attributes = ActionView_Helpers_TagHelper_NS::input_attributes(null, 'submit', $value, $attributes);
  return tag('input', $attributes);
}

?>
