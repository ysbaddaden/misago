<?php
/**
 * 
 * 
 * @package ActiveRecord
 * @subpackage Validations
 */
abstract class ActiveRecord_Validations extends ActiveRecord_Associations
{
  # Validates the presence of an attribute.
  # The attribute must be present and it cannot be blank.
  # 
  # - on: validate on 'create' or 'update' only
  # - message: generic error message
  protected $validates_presence_of = array();
  
  # Validates the length of an attribute.
  # 
  # - on: validate on 'create' or 'update' only
  # - allow_null: if true allows the attribute to be null.
  # - minimum: int
  # - maximum: int
  # - within: 'min..max'
  # - is: int
  # - message: generic error message
  # - too_short: error message when length < minimum
  # - too_long: error message when length > maximum
  # - wrong_length: error message when length isn't exactly the 'is' size.
  protected $validates_length_of   = array();
  
  
  function __get($attribute)
  {
    if ($attribute == 'errors') {
      return $this->errors = new ActiveRecord_Errors();
    }
    return parent::__get($attribute);
  }
  
  # Runs validation tests. Returns true if tests were successfull, false otherwise.
  function is_valid()
  {
    $on = $this->new_record ? 'create' : 'update';
    $validate_on = "validate_on_$on";
    
    $this->errors->clear();
    
    $this->automated_validation('validates_presence_of', $on);
    $this->automated_validation('validates_length_of',   $on);
    
    $this->validate();
    $this->$validate_on();
    
    return $this->errors->is_empty();
  }
  
  # Validates record's attributes on creation as well as on update.
  protected function validate() {}

  # Validates record's attributes on creation only.
  protected function validate_on_create() {}

  # Validates record's attributes on update only.
  protected function validate_on_update() {}
  
  
  private function automated_validation($rules, $on)
  {
    $action = "_{$rules}";
    foreach($this->$rules as $attribute => $options)
    {
      if (is_numeric($attribute))
      {
        $attribute = $options;
        $options   = array();
      }
      elseif (isset($options['on']) and $options['on'] != $on) {
        continue;
      }
      $this->$action($attribute, $options);
    }
  }
  
  private function _validates_presence_of($attribute, $options=null)
  {
    if (!isset($this->$attribute) or is_blank($this->$attribute)) {
      $this->errors->add($attribute, isset($options['message']) ? $options['message'] : ':blank');
    }
  }
  
  private function _validates_length_of($attribute, $options=null)
  {
    if (isset($options['within']))
    {
      preg_match('/^([\d\-\s]+)\.\.([\d\-\s]+)$/', $options['within'], $match);
      $options['minimum'] = $match[1];
      $options['maximum'] = $match[2];
    }
    if (!isset($options['maximum']) and !empty($this->columns[$attribute]['limit'])) {
      $options['maximum'] = $this->columns[$attribute]['limit'];
    }
    if (!isset($options['allow_null']) and !empty($this->columns[$attribute]['null'])) {
      $options['allow_null'] = $this->columns[$attribute]['null'];
    }
    if (empty($this->$attribute)
      and isset($options['allow_null'])
      and $options['allow_null'])
    {
      return;
    }
    
    # length depends on var type
    switch(gettype($this->$attribute))
    {
      case 'object':
        if ($this->$attribute instanceof Time)
        {
          $length = $this->$attribute->to_timestamp();
          if (isset($options['minimum']))
          {
            $t = new Time($options['minimum']);
            $options['minimum'] = $t->to_timestamp();
          }
          if (isset($options['maximum']))
          {
            $t = new Time($options['maximum']);
            $options['maximum'] = $t->to_timestamp();
          }
          if (isset($options['is']))
          {
            $t = new Time($options['is']);
            $options['is'] = $t->to_timestamp();
          }
        }
        else {
          trigger_error("Unsupported object type: ".get_class($this->$attribute), E_USER_WARNING);
        }
      break;
      case 'string': $length = strlen($this->$attribute); break;
      default: $length = $this->$attribute;
    }
    
    # validation
    if (isset($options['minimum']) and $length < $options['minimum']) {
      $message = 'too_short';
    }
    elseif (isset($options['maximum']) and $length > $options['maximum']) {
      $message = 'too_long';
    }
    elseif (isset($options['is']) and $length != $options['is']) {
      $message = 'wrong_length';
    }
    
    # reports error
    if (isset($message))
    {
      $message = isset($options[$message]) ? $options[$message] : (
        isset($options['message']) ? $options['message'] : ":$message");
      $this->errors->add($attribute, $message);
    }
  }
}

?>
