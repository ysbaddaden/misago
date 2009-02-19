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
  
  function & column_names()
  {
    $column_names = array_keys($this->table_columns);
    return $column_names;
  }
}

?>
