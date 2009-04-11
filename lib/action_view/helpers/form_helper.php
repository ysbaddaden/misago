<?php

# IMPROVE: Transparently protect against CSRF attacks.
# TODO: Test start(), end() and submit() methods.
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
  
  function submit($value=null, $name=null, $attributes=null) {
    return html::submit($value, $name, $attributes);
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
