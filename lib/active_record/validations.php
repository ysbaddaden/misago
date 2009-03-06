<?php
/**
 * 
 * 
 * @package ActiveRecord
 * @subpackage Validations
 * 
 * TODO: Write shortcut validations (validates_xxx).
 */
class ActiveRecord_Validations extends ActiveRecord_Associations
{
  protected $validations = array();
  /*
  protected $validates_associated;
  protected $validates_each;
  protected $validates_format_of;
  protected $validates_exclusion_of;
  protected $validates_inclusion_of;
  protected $validates_length_of;
  protected $validates_presence_of;
  protected $validates_uniqueness_of;
  */
  
  function __get($attribute)
  {
    if ($attribute == 'errors') {
      return $this->errors = new ActiveRecord_Errors();
    }
    return parent::__get($attribute);
  }
  
  function is_valid()
  {
    $this->errors->clear();
    
    /*
    $vars = get_class_vars(self);
    foreach($vars as $var)
    {
      if (strpos($var, 'validates_')
        and !empty($this->$var))
      {
        $this->{$var}();
      }
    }
    */
    
    $this->validate();
    
    $action = $this->new_record ? 'validate_on_create' : 'validate_on_update';
    $this->$action();
    
    return $this->errors->is_empty();
  }
  
  protected function validate() {}
  protected function validate_on_create() {}
  protected function validate_on_update() {}
  
  /*
  private function validates_associated()
  {
    
  }
  
  private function validates_each()
  {
    
  }
  
  private function validates_format_of()
  {
    
  }
  
  private function validates_exclusion_of()
  {
    
  }
  
  private function validates_inclusion_of()
  {
    
  }
  
  private function validates_length_of()
  {
    
  }
  
  private function validates_presence_of()
  {
    
  }
  
  private function validates_uniqueness_of()
  {
    
  }
  */
}

?>
