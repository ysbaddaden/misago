<?php

# Validation methods for ActiveRecords.
# 
# @package ActiveRecord
# @subpackage Validations
# 
# TODO: Test custom error messages for validates_length_of.
#
abstract class ActiveRecord_Validations extends ActiveRecord_Associations
{
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
    
    $this->$validate_on();
    $this->validate();
    
    return $this->errors->is_empty();
  }
  
  # Validates the presence of an attribute.
  # The attribute must be present and it cannot be blank.
  # 
  # - on: validate on 'create' or 'update' only
  # - message: error message
  protected function validates_presence_of($attribute, $options=null) {
    $this->_validates_presence_of($attribute, $options);
  }
  
  # Validates the length of an attribute.
  # 
  # - on: validate on 'create' or 'update' only
  # - allow_null: allows the attribute to be null (defaults to nullity in DB).
  # - allow_blank: allows the attribute to be blank (defaults to false).
  # - minimum: int
  # - maximum: int
  # - within: 'min..max'
  # - is: int
  # - message: generic error message.
  # - too_short: error message when length < minimum.
  # - too_long: error message when length > maximum.
  # - wrong_length: error message when length isn't exactly the 'is' size.
  protected function validates_length_of($attribute, $options=null) {
    $this->call_validation('validates_length_of', $attribute, $options);
  }
  
  # Validates the format of an attribute, using a regular expression.
  # 
  # - on: validate on 'create' or 'update' only
  # - allow_null: allows the attribute to be null (defaults to nullity in DB).
  # - allow_blank: allows the attribute to be blank (defaults to false).
  # - message: error message.
  # - with: the regular expression to use.
  protected function validates_format_of($attribute, $options=null) {
    $this->call_validation('validates_format_of', $attribute, $options);
  }
  
  # Validates if an attribute is within a list of values.
  # 
  # - on: validate on 'create' or 'update' only
  # - allow_null: allows the attribute to be null (defaults to nullity in DB).
  # - allow_blank: allows the attribute to be blank (defaults to false).
  # - message: generic error message.
  # - in: an enumerable list of values.
  protected function validates_inclusion_of($attribute, $options=null) {
    $this->call_validation('validates_inclusion_of', $attribute, $options);
  }
  
  # Validates if an attribute isn't within a list of values.
  # 
  # - on: validate on 'create' or 'update' only
  # - allow_null: allows the attribute to be null (defaults to nullity in DB).
  # - allow_blank: allows the attribute to be blank (defaults to false).
  # - message: error message.
  # - in: an enumerable list of values.
  protected function validates_exclusion_of($attribute, $options=null) {
    $this->call_validation('validates_exclusion_of', $attribute, $options);
  }
  
  # Validates if a column is unique.
  #
  # On create the check checks if the column isn't already present in the
  # database. On update it does the same, but excluding the record itself.
  # 
  # - on: validate on 'create' or 'update' only
  # - allow_null: allows the attribute to be null (defaults to nullity in DB).
  # - allow_blank: allows the attribute to be blank (defaults to false).
  # - message: error message.
  protected function validates_uniqueness_of($attribute, $options=null) {
    $this->call_validation('validates_uniqueness_of', $attribute, $options);
  }
  
  
  # Validates record's attributes on creation as well as on update.
  protected function validate() {}
	
  # Validates record's attributes on creation only.
  protected function validate_on_create() {}
	
  # Validates record's attributes on update only.
  protected function validate_on_update() {}
  
  
  private function call_validation($action, $attribute, $options=null)
  {
    if (!isset($options['allow_null'])
      and isset($this->columns[$attribute], $this->columns[$attribute]['null']))
    {
      $options['allow_null'] = $this->columns[$attribute]['null'];
    }
    if (isset($options['allow_null']) and $options['allow_null'] and !isset($this->$attribute)) {
      return;
    }
    if (isset($options['allow_blank']) and $options['allow_blank'] and is_blank($this->$attribute)) {
      return;
    }
    $action = "_$action";
    $this->$action($attribute, &$options);
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
      
      case 'string':
      	$length = strlen($this->$attribute);
      break;
      
      default:
      	$length = $this->$attribute;
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
  
  private function _validates_inclusion_of($attribute, $options=null)
  {
    foreach($options['in'] as $v)
    {
      if ($this->$attribute == $v) {
        return;
      }
    }
    $this->errors->add($attribute, isset($options['message']) ? $options['message'] : ":not_included");
  }
  
  private function _validates_exclusion_of($attribute, $options=null)
  {
    foreach($options['in'] as $v)
    {
      if ($this->$attribute == $v)
      {
        $this->errors->add($attribute, isset($options['message']) ? $options['message'] : ":reserved");
        return;
      }
    }
  }
  
  private function _validates_format_of($attribute, $options=null)
  {
    if (!preg_match($options['with'], $this->$attribute)) {
      $this->errors->add($attribute, isset($options['message']) ? $options['message'] : ":invalid");
    }
  }
  
  private function _validates_uniqueness_of($attribute, $options=null)
  {
    if (!$this->new_record)
    {
      $conditions = array("$attribute = :value AND {$this->primary_key} <> :id", array(
        'value' => $this->$attribute,
        'id'    => $this->id
      ));
    }
    else {
      $conditions = array($attribute => $this->$attribute);
    }
    
    $record = $this->find(':first', array(
    	'select'     => $this->primary_key,
    	'conditions' => &$conditions,
    ));
		if ($record and $record->id) {
			$this->errors->add($attribute, isset($options['message']) ? $options['message'] : ":taken");
		}
  }
}

?>
