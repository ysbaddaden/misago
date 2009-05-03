<?php

# IMPROVE: Transparently protect against CSRF attacks (using a hash with the key stored in a cookie).
# TODO: Test start(), end() and submit() methods.
# 
# @package ActionView
# @subpackage Helpers
class FormHelper
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
  
  function start($url, $options=null)
  {
    return html::form_tag($url, $options);
  }
  
  function end()
  {
    return '</form>';
  }
  
  function submit($value=null, $name=null, $attributes=null)
  {
    return html::submit($value, $name, $attributes);
  }
  
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
  
  /**
   * form::label('Product', 'in_stock');
   * form::label('Product', 'in_stock', 'In stock?');
   * form::label('Product', 'in_stock', 'In stock?', array('class' => 'available'));
   * form::label('Invoice', 'address', null, array('class' => 'invoice-address'));
   * form::label('Invoice', 'address', array('class' => 'invoice-address'));
   */
  function label($column, $text=null, $attributes=null)
  {
    $this->preparse_attributes($attributes);
    return form::label($this->object, $column, $text, $attributes);
  }
  
  function hidden_field($column, $attributes=null)
  {
    $this->preparse_attributes($attributes);
    return form::hidden_field($this->object, $column, $attributes);
  }
  
  function text_field($column, $attributes=null)
  {
    $this->preparse_attributes($attributes);
    return form::text_field($this->object, $column, $attributes);
  }
  
  function text_area($column, $attributes=null)
  {
    $this->preparse_attributes($attributes);
    return form::text_area($this->object, $column, $attributes);
  }
  
  function password_field($column, $attributes=null)
  {
    $this->preparse_attributes($attributes);
    return form::password_field($this->object, $column, $attributes);
  }
  
  function check_box($column=null, $attributes=null)
  {
    $this->preparse_attributes($attributes);
    return form::check_box($this->object, $column, $attributes);
  }
  
  function radio_button($column, $tag_value, $attributes=null)
  {
    $this->preparse_attributes($attributes);
    return form::radio_button($this->object, $column, $tag_value, $attributes);
  }
  
  function select($column, $options, $attributes=null)
  {
    $this->preparse_attributes($attributes);
    return form::select($this->object, $column, $options, $attributes);
  }
  
  function preparse_attributes(&$attributes)
  {
    if (!isset($attributes['index']) and isset($this->index)) {
      $attributes['index'] = $this->index;
    }
  }
}

function fields_for($record_or_name, $args=null) {
  return new FormHelper($record_or_name, $args);
}

function form_for($record_or_name, $args=null) {
  return new FormHelper($record_or_name, $args);
}

?>
