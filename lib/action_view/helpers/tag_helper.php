<?php

# A collection of helpful functions to render HTML content.
# 
# TODO: Move URL functions to UrlHelper.
class ActionView_Helpers_TagHelper_Klass
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
# @namespace ActionView_Helpers_TagHelper
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
  
  $attributes = ActionView_Helpers_TagHelper_Klass::parse_attributes($attributes);
  
  if ($inline_tag) {
    return "<$name$attributes/>";
  }
  return "<$name$attributes>$content</$name>";
}

# Renders a CDATA section.
# 
# @namespace ActionView_Helpers_TagHelper
function cdata_section($content)
{
  return "<![CDATA[$content]]>";
}

?>
