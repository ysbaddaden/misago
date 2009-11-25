<?php
namespace Misago\ActiveRecord;
use Misago\ActiveSupport;
use Misago\ActiveSupport\String;
use Misago\I18n;

# Ephemeral Record.
# 
# This is for tableless records. You get all the joys of an <tt>Misago\ActiveRecord</tt>
# (record, validation), without the need to store data. 
abstract class Ephemeral extends Validations
{
  function __set($attribute, $value)
  {
    if (static::has_column($attribute))
    {
      if ($value !== null)
      {
        switch($this->columns[$attribute]['type'])
        {
          case 'integer': $value = (int)$value;    break;
          case 'float':   $value = (double)$value; break;
          case 'boolean': $value = (bool)$value;   break;
          case 'datetime':
            if (!($value instanceof ActiveSupport\Datetime)) {
              $value = new ActiveSupport\Datetime($value);
            }
          break;
          case 'date':
            if (!($value instanceof ActiveSupport\Date)) {
              $value = new ActiveSupport\Date($value);
            }
          break;
          case 'time':
            if (!($value instanceof ActiveSupport\Time)) {
              $value = new ActiveSupport\Time($value);
            }
          break;
        }
      }
    }
    return parent::__set($attribute, $value);
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
