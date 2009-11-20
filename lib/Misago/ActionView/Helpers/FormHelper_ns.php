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
namespace Misago\ActionView\Helpers\FormHelper;
use Misago\ActiveSupport\String;

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

?>
