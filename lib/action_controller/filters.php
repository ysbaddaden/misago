<?php

abstract class ActionController_Filters extends ActionController_Rescue
{
  protected $before_filters = array();
  protected $after_filters  = array();
  
  protected function process_before_filters()
  {
    foreach($this->before_filters as $method)
    {
      $rs = $this->$method();
      
      if ($rs === false) {
        throw new ActionController_FailedFilter();
      }
    }
  }
  
  protected function process_after_filters()
  {
    foreach($this->after_filters as $method)
    {
      $rs = $this->$method();
      
      if ($rs === false) {
        throw new ActionController_FailedFilter();
      }
    }
  }
}

# @private
class ActionController_FailedFilter extends Exception
{
  function __construct() {
    parent::__construct('', 0);
  }
}

?>
