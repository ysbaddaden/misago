<?php

# TODO: Test form helper functions!
# @package ActionView
# @subpackage Helpers
class form
{
  static function label($object, $column, $text=null, $attributes=null)
  {
    list($name, $attributes['for']) = self::generate_names($object, $column);
    if ($text === null) {
      $text = String::humanize($column);
    }
    return html::label($name, $text, $attributes);
  }

  static function hidden_field($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::generate_names($object, $column);
    return html::hidden_field($name, is_object($object) ? $object->$column : '', $attributes);
  }

  static function text_field($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::generate_names($object, $column);
    return html::text_field($name, is_object($object) ? $object->$column : '', $attributes);
  }

  static function text_area($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::generate_names($object, $column);
    return html::text_area($name, is_object($object) ? $object->$column : '', $attributes);
  }

  /**
   * Gotcha: an unchecked checkbox is never sent. A solution if to
   * add a hidden field with the same name before the checkbox. If
   * the box is unchecked, the hidden field's value will be sent, if
   * it's checked PHP will overwrite the hidden field's value. 
   */
  static function check_box($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::generate_names($object, $column);
    if (is_object($object)
      and $object->$column)
    {
      $attributes['checked'] = true;
    }
    $str  = html::tag('input', array('type' => 'hidden', 'name' => $name, 'value' => 0));
    $str .= html::check_box($name, 1, $attributes);
    return $str;
  }

  static function radio_button($object, $column, $tag_value, $attributes=null)
  {
    list($name, $attributes['id']) = self::generate_names($object, $column);
    if (is_object($object)
      and $object->$column == $tag_value)
    {
      $attributes['checked'] = true;
    }
    return html::radio_button($name, 1, $attributes);
  }
  
  private static function generate_names($object, $column)
  {
    $record_name = is_object($object) ? get_class($object) : $object;
    $record_name = String::underscore($record_name);
    
    $name = "{$record_name}[{$column}]";
    $id   = "{$record_name}_{$column}";
    
    $rs = array($name, $id);
    return $rs;
  }
}

?>
