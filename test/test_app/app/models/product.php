<?php

class Product extends ActiveRecord_Base
{
  function new_record()
  {
    return $this->new_record;
  }
  
  function columns()
  {
    return $this->columns;
  }
  
  /*
  function & column_names()
  {
    $column_names = array_keys($this->table_columns);
    return $column_names;
  }
  */
  
  function validate()
  {
    if (empty($this->name)) {
      $this->errors->add_on_empty('name');
    }
    if (is_blank($this->name)) {
      $this->errors->add_on_blank('name');
    }
    if (is_blank($this->price)) {
      $this->errors->add_on_blank('price');
    }
  }
}

?>
