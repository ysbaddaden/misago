<?php
/**
 * 
 * 
 * @package ActiveRecord
 * @subpackage Validations
 */
abstract class ActiveRecord_Validations extends ActiveRecord_Associations
{
  protected $validates_presence_of = array();
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
    $this->errors->clear();
    
    $this->automated_validation('validates_presence_of', $on);
    $this->validate();
    
    $action = "validate_on_$on";
    $this->$action();
    
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
}

?>
