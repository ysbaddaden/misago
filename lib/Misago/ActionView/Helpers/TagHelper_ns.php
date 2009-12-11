<?php

# A collection of helpful functions to render HTML content.
namespace Misago\ActionView\Helpers\TagHelper;

# Parses attributes for form input fields. Creates +id+ from +name+;
# quotes the +value+ attribute; parses the +disabled+ and +checked+
# options, etc.
# 
# :nodoc:
function input_attributes($name, $type, $value, $attributes)
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
  
  # special name => bool attributes
  foreach(array('disabled', 'autofocus', 'required') as $attr)
  {
    if (isset($attributes[$attr]))
    {
      if ($attributes[$attr]) {
        $attributes[$attr] = $attr;
      }
      else {
        unset($attributes[$attr]);
      }
    }
  }
  
  # checked?
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
# :nodoc:
function parse_attributes($attributes)
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

?>
