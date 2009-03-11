<?php

class FormHelper
{
  function __construct($record_or_name)
  {
    $this->record = is_object($record_or_name) ? $record_or_name : new $record_or_name();
  }
}

function form_for($record_or_name, $args=null)
{
  return new FormHelper($record_or_name);
}

?>
