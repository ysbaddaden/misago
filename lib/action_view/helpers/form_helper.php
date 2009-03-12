<?php

# TODO: Continue to test FormHelper class!
class FormHelper
{
  protected $object;
  
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
    return form::label($this->object, $column, $text, $attributes);
  }
  
  function hidden_field($column, $attributes=null)
  {
    return form::hidden_field($this->object, $column, $attributes);
  }
  
  function text_field($column, $attributes=null)
  {
    return form::text_field($this->object, $column, $attributes);
  }
  
  function text_area($column, $attributes=null)
  {
    return form::text_area($this->object, $column, $attributes);
  }
  
  function check_box($column=null, $attributes=null)
  {
    return form::check_box($this->object, $column, $attributes);
  }
  
  function radio_button($column, $tag_value, $attributes=null)
  {
    return form::radio_button($this->object, $column, $tag_value, $attributes);
  }
}

# TODO: Test fields_for
function fields_for($record_or_name)
{
  return new FormHelper($record_or_name);
}

?>
