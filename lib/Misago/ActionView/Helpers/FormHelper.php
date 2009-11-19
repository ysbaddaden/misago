<?php
# Helpful functions to render form fields for a model.
# 
#   <\? $search = new Search() ?\>
#   <\?= form_tag(search_path()) ?\>
#     <p>
#       <\?= label($search, 'query') ?\>
#       <\?= text_field($search, 'query') ?\>
#       <\?= submit_tag() ?\>
#     </p>
#   </form>
# 
namespace Misago\ActionView\Helpers\FormHelper
{
  class FormBuilder
  {
    protected $object;
    protected $index;
    
    function __construct($record_or_name, $args=null)
    {
      if (is_object($record_or_name)) {
        $this->object = $record_or_name;
      }
      else
      {
        $class = String::camelize($record_or_name);
        $this->object = new $class;
      }
      
      if (isset($args['index'])) {
        $this->index = $args['index'];
      }
    }
    
    # Starts the HTML form.
    function start($url, $options=null)
    {
      return form_tag($url, $options);
    }
    
    # Ends the HTML form.
    function end()
    {
      return '</form>';
    }
    
    # Displays errors related to a column.
    # Shows only the first error by default.
    function error_message_on($column, $all=false)
    {
      return error_message_on($this->object, $column, $all);
    }
    
    # Displays errors related to the record itself (as well as other objects).
    function error_messages_for($object=null)
    {
      $objects = func_get_args();
      array_unshift($objects, $this->object);
      return call_user_func_array('error_messages_for', $objects);
    }
    
    # DEPRECATED: use <tt>error_message_on</tt> instead.
    function errors_on($column, $all=false)
    {
      return $this->error_message_on($column, $all);
    }
    
    # DEPRECATED: use <tt>error_messages_for</tt> instead.
    function errors_on_base()
    {
      return $this->error_messages_for();
    }
    
    # Renders a label for a column.
    # 
    #   $f->label('in_stock');
    #   $f->label('in_stock', 'In stock?');
    #   $f->label('in_stock', 'In stock?', array('class' => 'available'));
    #   $f->label('address', array('class' => 'invoice-address'));
    function label($column, $text=null, $attributes=null)
    {
      $this->preparse_attributes($attributes);
      return label($this->object, $column, $text, $attributes);
    }
    
    # Renders a hidden field.
    function hidden_field($column, $attributes=null)
    {
      $this->preparse_attributes($attributes);
      return hidden_field($this->object, $column, $attributes);
    }
    
    # Renders a text input field.
    function text_field($column, $attributes=null)
    {
      $this->preparse_attributes($attributes);
      return text_field($this->object, $column, $attributes);
    }
    
    # Renders a text area.
    function text_area($column, $attributes=null)
    {
      $this->preparse_attributes($attributes);
      return text_area($this->object, $column, $attributes);
    }
    
    # Renders a password field.
    function password_field($column, $attributes=null)
    {
      $this->preparse_attributes($attributes);
      return password_field($this->object, $column, $attributes);
    }
    
    # Renders a checkable box.
    function check_box($column=null, $attributes=null)
    {
      $this->preparse_attributes($attributes);
      return check_box($this->object, $column, $attributes);
    }
    
    # Renders a radio button.
    function radio_button($column, $tag_value, $attributes=null)
    {
      $this->preparse_attributes($attributes);
      return radio_button($this->object, $column, $tag_value, $attributes);
    }
    
    # Renders a select field.
    function select($column, $options, $attributes=null)
    {
      $this->preparse_attributes($attributes);
      return select($this->object, $column, $options, $attributes);
    }
    
    # :private:
    protected function preparse_attributes(&$attributes)
    {
      if (!isset($attributes['index']) and isset($this->index)) {
        $attributes['index'] = $this->index;
      }
    }
  }
  
  # :nodoc:
  function format_name_and_id($object, $column, &$attributes=null)
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
