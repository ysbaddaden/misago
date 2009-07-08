<?php

# Helpful functions to render form fields for a model.
# 
# Example:
# 
#   <? $search = new Search(); ?\>
#   <?= html::form_tag(search_path()) ?\>
#     <p>
#       <?= form::label($search, 'query') ?\>
#       <?= form::text_field($search, 'query') ?\>
#       <?= html::submit() ?\>
#     </p>
#   </form>
# 
# NOTE: form functions are tested throught RecordHelper tests.
# 
# @package ActionView
# @subpackage Helpers
class form
{
  # Renders a label tag.
  # 
  #   form::label('Product', 'in_stock');
  #   form::label('Product', 'in_stock', 'In stock?');
  #   form::label('Product', 'in_stock', 'In stock?', array('class' => 'available'));
  #   form::label('Invoice', 'address', null, array('class' => 'invoice-address'));
  #   form::label('Invoice', 'address', array('class' => 'invoice-address'));
  static function label($object, $column, $text=null, $attributes=null)
  {
    if (is_array($text))
    {
      $attributes = $text;
      $text = null;
    }
    if ($text === null) {
      $text = $object->human_attribute_name($column);
    }
    list($name, $attributes['for']) = self::format_name_and_id($object, $column, $attributes);
    return html::label($name, $text, $attributes);
  }

  # Renders a hidden field.
  static function hidden_field($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::format_name_and_id($object, $column, $attributes);
    return html::hidden_field($name, is_object($object) ? $object->$column : '', $attributes);
  }
  
  # Renders a text field.
  static function text_field($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::format_name_and_id($object, $column, $attributes);
    return html::text_field($name, is_object($object) ? $object->$column : '', $attributes);
  }
  
  # Renders a password field.
  static function password_field($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::format_name_and_id($object, $column, $attributes);
    return html::password_field($name, /*is_object($object) ? $object->$column :*/ '', $attributes);
  }
  
  # Renders a text area.
  static function text_area($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::format_name_and_id($object, $column, $attributes);
    return html::text_area($name, is_object($object) ? $object->$column : '', $attributes);
  }
  
  # Renders a check box.
  #
  # Gotcha: an unchecked check box is never sent. A solution if to
  # add a hidden field with the same name before the check box. If
  # the box is unchecked, the hidden field's value will be sent;
  # if checked PHP will overwrite the hidden field's value. 
  static function check_box($object, $column, $attributes=null)
  {
    list($name, $attributes['id']) = self::format_name_and_id($object, $column, $attributes);
    if (is_object($object)
      and $object->$column)
    {
      $attributes['checked'] = true;
    }
    $str  = html::tag('input', array('type' => 'hidden', 'name' => $name, 'value' => 0));
    $str .= html::check_box($name, 1, $attributes);
    return $str;
  }
  
  # Renders a radio button.
  static function radio_button($object, $column, $tag_value, $attributes=null)
  {
    list($name, $id) = self::format_name_and_id($object, $column, $attributes);
    $attributes['id'] = "{$id}_{$tag_value}";
    
    if (is_object($object)
      and $object->$column == $tag_value)
    {
      $attributes['checked'] = true;
    }
    return html::radio_button($name, $tag_value, $attributes);
  }
  
  # Renders a select option field.
  static function select($object, $column, $options, $attributes=null)
  {
    list($name, $attributes['id']) = self::format_name_and_id($object, $column, $attributes);
    $value   = is_object($object) ? $object->$column : null;
    $options = self::options_for_select($options, $value);
    return html::select($name, $options, $attributes);
  }
  
  # Parses options for a select option field.
  static function options_for_select($options, $selected=null)
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
  
  private static function format_name_and_id($object, $column, &$attributes=null)
  {
    $record_name = is_object($object) ? get_class($object) : $object;
    $record_name = String::underscore($record_name);
    
    if (isset($attributes['index']))
    {
      $name = "{$record_name}[{$attributes['index']}][{$column}]";
      $id   = "{$record_name}_{$attributes['index']}_{$column}";
      unset($attributes['index']);
    }
    else
    {
      $name = "{$record_name}[{$column}]";
      $id   = "{$record_name}_{$column}";
    }
    $rs = array($name, $id);
    return $rs;
  }
}

?>
