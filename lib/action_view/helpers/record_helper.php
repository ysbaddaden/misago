<?php

# Helper object to create a HTML form for a record.
# 
# Initiate helper for a class:
# 
#   $f = form_for('User');
# 
# Initiate helper for an instance:
# 
#   $user = new User(456);
#   $f = form_for($user);
# 
# Build a form:
# 
#   $f->start(update_user_path($user->id))
#   $f->label('username');
#   $f->text_field('username');
#   $f->submit('Save');
#   $f->end();
# 
# You may also mix multiple records:
# 
#   $p = fields_for($user->profile);
#   $f->start(update_user_path($user->id))
#   
#   $f->label('username');
#   $f->text_field('username');
#   
#   $p->label('about');
#   $p->text_field('about');
#   
#   $f->submit('Save');
#   $f->end();
# 
# IMPROVE: Transparently protect against CSRF attacks (using a hash stored in a cookie/session).
# 
class ActionView_Helpers_RecordHelper_NS
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
  
  /*
  # Displays a submit button.
  function submit($value=null, $name=null, $attributes=null)
  {
    return submit_tag($value, $name, $attributes);
  }
  */
  
  # Displays errors related to a column.
  # Shows only the first error by default.
  function errors_on($column, $all=false)
  {
    if ($this->object->errors->is_invalid($column))
    {
      $errors = $this->object->errors->on($column);
      if (!is_array($errors)) {
        $errors = array($errors);
      }
      if ($all)
      {
        $str = "";
        foreach($errors->on($column) as $err) {
          $str .= "$err<br/>";
        }
        return "<span class=\"error\">{$str}</span>";
      }
      else {
        return "<span class=\"error\">{$errors[0]}</span>";
      }
    }
  }
  
  # Displays errors related to the record itself.
  function errors_on_base()
  {
    $errors = $this->object->errors->on_base();
    if (!empty($errors))
    {
      if (!is_array($errors)) {
        $errors = array($errors);
      }
      
      $str = "";
      foreach($errors as $err) {
        $str .= "<li>$err</li>";
      }
      return "<ul class=\"errors\">{$str}</ul>";
    }
  }
  
  # Renders a label for a column.
  # 
  #   form::label('Product', 'in_stock');
  #   form::label('Product', 'in_stock', 'In stock?');
  #   form::label('Product', 'in_stock', 'In stock?', array('class' => 'available'));
  #   form::label('Invoice', 'address', null, array('class' => 'invoice-address'));
  #   form::label('Invoice', 'address', array('class' => 'invoice-address'));
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
  
  protected function preparse_attributes(&$attributes)
  {
    if (!isset($attributes['index']) and isset($this->index)) {
      $attributes['index'] = $this->index;
    }
  }
}

# @namespace ActionView_Helpers_RecordHelper
function fields_for($record_or_name, $args=null) {
  return new ActionView_Helpers_RecordHelper_NS($record_or_name, $args);
}

# @namespace ActionView_Helpers_RecordHelper
function form_for($record_or_name, $args=null) {
  return new ActionView_Helpers_RecordHelper_NS($record_or_name, $args);
}

?>
