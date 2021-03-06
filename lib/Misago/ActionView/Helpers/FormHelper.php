<?php
require __DIR__.'/FormHelper_ns.php';

# :namespace: Misago\ActionView\Helpers\FormHelper
function form_for($record_or_name, $args=null) {
  return new Misago\ActionView\Helpers\FormHelper\FormBuilder($record_or_name, $args);
}

# :namespace: Misago\ActionView\Helpers\FormHelper
function fields_for($record_or_name, $args=null) {
  return new Misago\ActionView\Helpers\FormHelper\FormBuilder($record_or_name, $args);
}

# Renders a label tag.
# 
#   label('Product', 'in_stock');
#   label('Product', 'in_stock', 'In stock?');
#   label('Product', 'in_stock', 'In stock?', array('class' => 'available'));
#   label('Invoice', 'address', null, array('class' => 'invoice-address'));
#   label('Invoice', 'address', array('class' => 'invoice-address'));
# 
# :namespace: Misago\ActionView\Helpers\FormHelper
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
  list($name, $attributes['for']) = Misago\ActionView\Helpers\FormHelper\format_name_and_id($object, $column, $attributes);
  return label_tag($name, $text, $attributes);
}

# Renders a hidden field.
# 
# :namespace: Misago\ActionView\Helpers\FormHelper
function hidden_field($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = Misago\ActionView\Helpers\FormHelper\format_name_and_id($object, $column, $attributes);
  return hidden_field_tag($name, is_object($object) ? $object->$column : '', $attributes);
}

# Renders a text field.
# 
# :namespace: Misago\ActionView\Helpers\FormHelper
function text_field($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = Misago\ActionView\Helpers\FormHelper\format_name_and_id($object, $column, $attributes);
  return text_field_tag($name, is_object($object) ? $object->$column : '', $attributes);
}

# Renders a password field.
# 
# :namespace: Misago\ActionView\Helpers\FormHelper
function password_field($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = Misago\ActionView\Helpers\FormHelper\format_name_and_id($object, $column, $attributes);
  return password_field_tag($name, /*is_object($object) ? $object->$column :*/ '', $attributes);
}

# Renders a text area.
# 
# :namespace: Misago\ActionView\Helpers\FormHelper
function text_area($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = Misago\ActionView\Helpers\FormHelper\format_name_and_id($object, $column, $attributes);
  return text_area_tag($name, is_object($object) ? $object->$column : '', $attributes);
}

# Renders a check box.
#
# Gotcha: an unchecked check box is never sent. A solution if to
# add a hidden field with the same name before the check box. If
# the box is unchecked, the hidden field's value will be sent;
# if checked PHP will overwrite the hidden field's value. 
# 
# :namespace: Misago\ActionView\Helpers\FormHelper
function check_box($object, $column, $attributes=null)
{
  list($name, $attributes['id']) = Misago\ActionView\Helpers\FormHelper\format_name_and_id($object, $column, $attributes);
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
# :namespace: Misago\ActionView\Helpers\FormHelper
function radio_button($object, $column, $tag_value, $attributes=null)
{
  list($name, $id) = Misago\ActionView\Helpers\FormHelper\format_name_and_id($object, $column, $attributes);
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
# :namespace: Misago\ActionView\Helpers\FormHelper
function select($object, $column, $options, $attributes=null)
{
  list($name, $attributes['id']) = Misago\ActionView\Helpers\FormHelper\format_name_and_id($object, $column, $attributes);
  $value   = is_object($object) ? $object->$column : null;
  $options = options_for_select($options, $value);
  return select_tag($name, $options, $attributes);
}

?>
