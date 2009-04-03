<?php

# TODO: Test FormHelper class!
class FormHelper
{
  protected $record;
  
  function __construct($record_or_name, $args=null)
  {
    if (is_object($record_or_name)) {
      $this->record = $record_or_name;
    }
    else
    {
      $class = String::camelize($record_or_name);
      $this->record = new $class;
    }
  }
  
  function label($column, $text=null, $attributes=null)
  {
    return form::label($this->record, $column, $text, $attributes);
  }
  
  function hidden_field($column, $attributes=null)
  {
    return form::hidden_field($this->record, $column, $attributes);
  }
  
  function text_field($column, $attributes=null)
  {
    return form::text_field($this->record, $column, $attributes);
  }
  
  function text_area($column, $attributes=null)
  {
    return form::text_area($this->record, $column, $attributes);
  }
  
  /**
   * Gotcha: an unchecked checkbox is never sent. A solution if to
   * add a hidden field with the same name before the checkbox. If
   * the box is unchecked, the hidden field's value will be sent, if
   * it's checked PHP will overwrite the hidden field's value. 
   */
  function check_box($column=null, $attributes=null)
  {
    return form::check_box($this->record, $column, $attributes);
  }
  
  function radio_button($column, $tag_value, $attributes=null)
  {
    return form::radio_button($this->record, $column, $tag_value, $attributes);
  }
}

function fields_for($record_or_name)
{
  return new FormHelper($record_or_name);
}

?>
