<?php

# Ephemeral Record.
# 
# This is for tableless records. You get all the joys of an <tt>ActiveRecord</tt>
# (record, validation), without the need to store data. 
abstract class ActiveRecord_Ephemeral extends ActiveRecord_Validations
{
  protected $behaviors = array();
  protected $columns   = array();
  
  function __set($attribute, $value)
  {
    if (isset($this->columns[$attribute]))
    {
      if ($value !== null)
      {
        switch($this->columns[$attribute]['type'])
        {
          case 'integer': $value = (int)$value;    break;
          case 'float':   $value = (double)$value; break;
          case 'boolean': $value = (bool)$value;   break;
          case 'date': case 'datetime': case 'time':
            if (!($value instanceof Time)) {
              $value = new Time($value, $this->columns[$attribute]['type']);
            }
          break;
        }
      }
    }
    return parent::__set($attribute, $value);
  }
  
  # Returns an array of column names.
  function & column_names()
  {
    $column_names = array_keys($this->columns);
    return $column_names;
  }
  
  # Returns the I18n translation of model name
  # (in +active_record.models context+).
  # Defaults to the <tt>String::humanize()</tt> method.
  function human_name()
  {
    $model = String::underscore(get_class($this));
    $human_name = I18n::translate($model, array('context' => "active_record.models"));
    return String::humanize($human_name);
  }
  
  # Returns the I18n translation of attribute name
  # (in +active_record.attributes.$model+ context).
  # Defaults to the <tt>String::humanize()</tt> method.
  function human_attribute_name($attribute)
  {
    $model = String::underscore(get_class($this));
    $human_name = I18n::translate($attribute, array('context' => "active_record.attributes.$model"));
    return String::humanize($human_name);
  }
}

?>
