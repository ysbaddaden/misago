<?php

# Creates a form opening tag.
# 
# Options:
# 
# - +multipart+ - sets +enctype+ to +multipart/form-data+;
# 
# If method is different from GET or POST, a hidden +_method+ field will be added.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function form_tag($url, $attributes=null)
{
  if (is_array($url)) {
    $url = cfg_get('misago.current_controller')->url_for($url);
  }
  
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
  $attributes = Misago\ActionView\Helpers\TagHelper\parse_attributes($attributes);
  
  $url = str_replace('&', '&amp;', $url);
  if ($method == 'get' or $method == 'post') {
    $str = "<form action=\"$url\" method=\"$method\"$attributes>";
  }
  else
  {
    $str  = "<form action=\"$url\" method=\"post\"$attributes>";
    $str .= '<input type="hidden" name="_method" value="'.$method.'"/>';
  }
  
  # Protection against request forgery (CSRF)
  if ($method != 'get'
    and Misago\ActionController\protect_against_forgery())
  {
    $str .= Misago\ActionController\token_tag();
  }
  return $str;
}

# Renders a form label.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function label_tag($name, $text=null, $attributes=null)
{
  if (empty($text)) {
    $text = Misago\ActiveSupport\String::humanize($name);
  }
  if (!isset($attributes['for'])) {
    $attributes['for'] = $name;
  }
  return tag('label', $text, $attributes);
}

# Renders a hidden form field.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function hidden_field_tag($name, $value=null, $attributes=null)
{
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes($name, 'hidden', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a text form field.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function text_field_tag($name, $value=null, $attributes=null)
{
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes($name, 'text', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a text form area.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function text_area_tag($name, $content=null, $attributes=null)
{
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes($name, null, null, $attributes);
  $content    = htmlspecialchars($content);
  return tag('textarea', $content, $attributes);
}

# Renders a password form field.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function password_field_tag($name, $value=null, $attributes=null)
{
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes($name, 'password', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a file upload form field.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function file_field_tag($name, $attributes=null)
{
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes($name, 'file', null, $attributes);
  return tag('input', $attributes);
}

# Renders a check box.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function check_box_tag($name, $value=1, $attributes=null)
{
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes($name, 'checkbox', $value, $attributes);
  return tag('input', $attributes);
}

# Renders a radio button.
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function radio_button_tag($name, $value, $attributes=null)
{
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes($name, 'radio', $value, $attributes);
  return tag('input', $attributes);
}

# Parses options for a select option field.
# 
# Render from a hash:
# 
#   $options = array(
#     'Keyboard' => 45,
#     'Mouse'    => 72,
#     'Scanner'  => 59,
#   );
#   $html_options = options_for_select($options, 45);
# 
# Render from an ActiveRecord resultset:
# 
#   $products = $post->find(':values', array('select' => 'id,name'));
#   $html_options = options_for_select($products, 59);
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function options_for_select($options, $selected=null)
{
  if ($selected === null) {
    $selected = array();
  }
  elseif (!is_array($selected)) {
    $selected = array($selected);
  }
  
  if (!is_hash($options))
  {
    $_options = array();
    foreach($options as $ary) {
      $_options[$ary[0]] = $ary[1];
    }
    $options =& $_options;
  }
  
  $str = '';
  foreach($options as $name => $value)
  {
    $attr = (in_array($value, $selected)) ? ' selected="selected"' : '';
    $str .= "<option value=\"$value\"$attr>$name</option>";
  }
  return $str;
}

# Renders a select option field.
# 
# +$options+ must be a string of OPTION tags. You may use <tt>options_for_select</tt>
# to build it.
# 
# Example:
# 
#   $options = array(
#     'Keyboard' => 45,
#     'Mouse'    => 72,
#     'Scanner'  => 59,
#   );
#   $selected = 45;
#   select_tag('type', options_for_select($options, $selected));
# 
# :namespace: Misago\ActionView\Helpers\FormTagHelper
function select_tag($name, $options=null, $attributes=null)
{
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes($name, null, null, $attributes);
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
# :namespace: Misago\ActionView\Helpers\FormTagHelper
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
  $attributes = Misago\ActionView\Helpers\TagHelper\input_attributes(null, 'submit', $value, $attributes);
  return tag('input', $attributes);
}

?>
