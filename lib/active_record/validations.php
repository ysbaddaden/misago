<?php

# Validation methods for ActiveRecords.
# 
# Example:
# 
#   class User extends ActiveRecord_Base
#   {
#     protected function validate()
#     {
#       $this->assert_uniqueness_of('user_name');
#     }
#     
#     protected function validate_on_create()
#     {
#       $this->assert_length_of('user_name', array('minimum' => 3, 'maximum' => 20));
#     }
#   }
# 
# = Calls
# 
# You may ask for a specific validation using <tt>is_valid()</tt> or when saving
# with <tt>save_with_validation()</tt>, which is the default for <tt>ActiveRecord_Base::save()</tt>.
# 
# = Error object
# 
# All validation errors are accessible throught the +errors+ attribute,
# which is an instance of <tt>ActiveRecord_Errors</tt>. You may also manually add
# errors throught that same object.
# 
# = Associations
# 
# Dependent associated records are also validated before saving. If any dependent
# record's validation fails, this record's validation will fail.
# 
# = Callbacks
# 
# See <tt>ActiveRecord_Base</tt> for help on the different callbacks.
# 
# TEST: Test validates_associated.
# IMPROVE: Add possibility to validate a date/time.
# IMPROVE: On update only validate changed attributes (check new record).
abstract class ActiveRecord_Validations extends ActiveRecord_Associations
{
  protected $validates_associated = array();
  
  function __get($attribute)
  {
    if ($attribute == 'errors') {
      return $this->errors = new ActiveRecord_Errors($this);
    }
    return parent::__get($attribute);
  }
  
  function save_with_validation($perform_validation=true)
  {
    if (($perform_validation and $this->is_valid()) or !$perform_validation) {
      return $this->save_without_validation();
    }
    return false;
  }
  
  function save_without_validation()
  {
    $method = $this->new_record ? '_create' : '_update';
    return (bool)$this->$method();
  }
  
  # Runs validation tests.
  # Returns true if tests were successfull, false otherwise.
  function is_valid()
  {
    $on = $this->new_record ? 'create' : 'update';
    $validate_on          = "validate_on_$on";
    $before_validation_on = "before_validation_on_$on";
    $after_validation_on  = "after_validation_on_$on";
    
    $this->errors->clear();
    
    $this->before_validation();
    $this->$before_validation_on();
    
    $this->validate();
    $this->$validate_on();
    
    $this->$after_validation_on();
    $this->after_validation();
    
    if (!$this->validate_associated()) {
      return false;
    }
    return $this->errors->is_empty();
  }
  
  # Validates given associations.
  private function validate_associated()
  {
    foreach($this->validates_associated as $assoc)
    {
      if (isset($this->$assoc) and !$this->$assoc->is_valid()) {
        $rs = false;
      }
    }
    return isset($rs) ? $rs : true;
  }
  
  # Validates the presence of an attribute.
  # The attribute must be present and it cannot be blank.
  # 
  # Options:
  # 
  # - +message+: the error message
  protected function validate_presence_of($attribute, $options=null) {
    $this->_validate_presence_of($attribute, $options);
  }
  
  # Validates the length of an attribute.
  # 
  # Options:
  # 
  # - +allow_null+   - allows the attribute to be null (defaults to nullity in DB).
  # - +allow_blank+  - allows the attribute to be blank (defaults to false).
  # - +minimum+      - int
  # - +maximum+      - int
  # - +within+       - 'min..max'
  # - +is+           - int
  # - +message+      - generic error message.
  # - +too_short+    - error message when length < minimum.
  # - +too_long+     - error message when length > maximum.
  # - +wrong_length+ - error message when length isn't exactly the +is+ size.
  protected function validate_length_of($attribute, $options=null) {
    $this->call_validation('validate_length_of', $attribute, $options);
  }
  
  # Validates the format of an attribute, using a regular expression.
  # 
  # Options:
  # 
  # - +allow_null+  - allows the attribute to be null (defaults to nullity in DB).
  # - +allow_blank+ - allows the attribute to be blank (defaults to false).
  # - +message+     - error message.
  # - +with+        - the regular expression to use.
  protected function validate_format_of($attribute, $options=null) {
    $this->call_validation('validate_format_of', $attribute, $options);
  }
  
  # Validates if an attribute is within a list of values.
  # 
  # Options:
  # 
  # - +allow_null+  - allows the attribute to be null (defaults to nullity in DB).
  # - +allow_blank+ - allows the attribute to be blank (defaults to false).
  # - +message+     - error message.
  # - +in+          - an enumerable list of values.
  protected function validate_inclusion_of($attribute, $options=null) {
    $this->call_validation('validate_inclusion_of', $attribute, $options);
  }
  
  # Validates if an attribute isn't within a list of values.
  # 
  # Options:
  # 
  # - +allow_null+  - allows the attribute to be null (defaults to nullity in DB).
  # - +allow_blank+ - allows the attribute to be blank (defaults to false).
  # - +message+     - error message.
  # - +in+          - an enumerable list of values.
  protected function validate_exclusion_of($attribute, $options=null) {
    $this->call_validation('validate_exclusion_of', $attribute, $options);
  }
  
  # Validates if a column is unique.
  #
  # On create checks if the column isn't already present in the database.
  # On update it does the same, but excluding the record itself.
  # 
  # Options:
  # 
  # - +allow_null+  - allows the attribute to be null (defaults to nullity in DB).
  # - +allow_blank+ - allows the attribute to be blank (defaults to false).
  # - +message+     - error message.
  protected function validate_uniqueness_of($attribute, $options=null) {
    $this->call_validation('validate_uniqueness_of', $attribute, $options);
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
    $this->$action($attribute, $options);
  }
  
  private function _validate_presence_of($attribute, $options=null)
  {
    if (!isset($this->$attribute) or is_blank($this->$attribute)) {
      $this->errors->add($attribute, isset($options['message']) ? $options['message'] : ':blank');
    }
  }
  
  private function _validate_length_of($attribute, $options=null)
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
        if ($this->$attribute instanceof ActiveSupport_Datetime)
        {
          $length = $this->$attribute->to_timestamp();
          if (isset($options['minimum']))
          {
            $t = new ActiveSupport_Datetime($options['minimum']);
            $options['minimum'] = $t->getTimestamp();
          }
          if (isset($options['maximum']))
          {
            $t = new ActiveSupport_Datetime($options['maximum']);
            $options['maximum'] = $t->getTimestamp();
          }
          if (isset($options['is']))
          {
            $t = new ActiveSupport_Datetime($options['is']);
            $options['is'] = $t->getTimestamp();
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
    if (isset($options['is']) and $length != $options['is']) {
      $message = 'wrong_length';
    }
    elseif (isset($options['maximum']) and $length > $options['maximum']) {
      $message = 'too_long';
    }
    elseif (isset($options['minimum']) and $length < $options['minimum']) {
      $message = 'too_short';
    }
    
    # reports error
    if (isset($message))
    {
      $message = isset($options[$message]) ? $options[$message] : (
        isset($options['message']) ? $options['message'] : ":$message");
      $this->errors->add($attribute, $message);
    }
  }
  
  private function _validate_inclusion_of($attribute, $options=null)
  {
    foreach($options['in'] as $v)
    {
      if ($this->$attribute == $v) {
        return;
      }
    }
    $this->errors->add($attribute, isset($options['message']) ? $options['message'] : ":not_included");
  }
  
  private function _validate_exclusion_of($attribute, $options=null)
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
  
  private function _validate_format_of($attribute, $options=null)
  {
    if (!preg_match($options['with'], $this->$attribute)) {
      $this->errors->add($attribute, isset($options['message']) ? $options['message'] : ":invalid");
    }
  }
  
  private function _validate_uniqueness_of($attribute, $options=null)
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
  
  protected function before_validation() {}
  protected function before_validation_on_create() {}
  protected function before_validation_on_update() {}
  
  protected function after_validation() {}
  protected function after_validation_on_create() {}
  protected function after_validation_on_update() {}
}

?>
