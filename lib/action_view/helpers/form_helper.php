<?php
/**
 * Helpful functions to render form fields for a model.
 * 
 * Example:
 * 
 *   <? $search = new Search() ?>
 *   <?= form_tag(search_path()) ?>
 *     <p>
 *       <?= label($search, 'query') ?>
 *       <?= text_field($search, 'query') ?>
 *       <?= submit_tag() ?>
 *     </p>
 *   </form>
 */
class ActionView_Helpers_FormHelper_NS
{
  # 
  # @private
  static function format_name_and_id($object, $column, &$attributes=null)
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

# Renders a label tag.
# 
#   form::label('Product', 'in_stock');
#   form::label('Product', 'in_stock', 'In stock?');
#   form::label('Product', 'in_stock', 'In stock?', array('class' => 'available'));
#   form::label('Invoice', 'address', null, array('class' => 'invoice-address'));
#   form::label('Invoice', 'address', array('class' => 'invoice-address'));
# 
# @namespace ActionView_Helpers_FormHelper
function label($object, $column, $text=null, $attributes=null)
{
  if (is_array($text))
  {
    $attributes = $text;
    $text = null;
  }
  if ($text === null) {
    $text = $object->human_attribute_name($column);
  }
  list($name, $attributes['for']) = ActionView_Helpers_FormHelper_NS::format_name_and_id($object, $column, $attributes);
  return label_tag($name, $text, $attributes);
}

# Renders a hidden field.
# 
# @namespace ActionView_Helpers_FormHelper
function hidden_field($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = ActionView_Helpers_FormHelper_NS::format_name_and_id($object, $column, $attributes);
  return hidden_field_tag($name, is_object($object) ? $object->$column : '', $attributes);
}

# Renders a text field.
# 
# @namespace ActionView_Helpers_FormHelper
function text_field($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = ActionView_Helpers_FormHelper_NS::format_name_and_id($object, $column, $attributes);
  return text_field_tag($name, is_object($object) ? $object->$column : '', $attributes);
}

# Renders a password field.
# 
# @namespace ActionView_Helpers_FormHelper
function password_field($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = ActionView_Helpers_FormHelper_NS::format_name_and_id($object, $column, $attributes);
  return password_field_tag($name, /*is_object($object) ? $object->$column :*/ '', $attributes);
}

# Renders a text area.
# 
# @namespace ActionView_Helpers_FormHelper
function text_area($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = ActionView_Helpers_FormHelper_NS::format_name_and_id($object, $column, $attributes);
  return text_area_tag($name, is_object($object) ? $object->$column : '', $attributes);
}

# Renders a check box.
#
# Gotcha: an unchecked check box is never sent. A solution if to
# add a hidden field with the same name before the check box. If
# the box is unchecked, the hidden field's value will be sent;
# if checked PHP will overwrite the hidden field's value. 
# 
# @namespace ActionView_Helpers_FormHelper
function check_box($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = ActionView_Helpers_FormHelper_NS::format_name_and_id($object, $column, $attributes);
  if (is_object($object)
    and $object->$column)
  {
    $attributes['checked'] = true;
  }
  $str  = tag('input', array('type' => 'hidden', 'name' => $name, 'value' => 0));
  $str .= check_box_tag($name, 1, $attributes);
  return $str;
}

# Renders a radio button.
# 
# @namespace ActionView_Helpers_FormHelper
function radio_button($object, $column, $tag_value, $attributes=null)
{
  list($name, $id) = ActionView_Helpers_FormHelper_NS::format_name_and_id($object, $column, $attributes);
  $attributes['id'] = "{$id}_{$tag_value}";
  
  if (is_object($object)
    and $object->$column == $tag_value)
  {
    $attributes['checked'] = true;
  }
  return radio_button_tag($name, $tag_value, $attributes);
}

# Renders a select option field.
# 
# @namespace ActionView_Helpers_FormHelper
function select($object, $column, $options, $attributes=null)
{
  list($name, $attributes['id']) = ActionView_Helpers_FormHelper_NS::format_name_and_id($object, $column, $attributes);
  $value   = is_object($object) ? $object->$column : null;
  $options = options_for_select($options, $value);
  return select_tag($name, $options, $attributes);
}

# Parses options for a select option field.
# 
# @namespace ActionView_Helpers_FormHelper
# TODO: Move options_for_select() to FormOptionsHelper.
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

?>
