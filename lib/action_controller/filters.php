<?php

abstract class ActionController_Filters extends ActionController_Rescue
{
  private $before_filters = array();
  private $after_filters  = array();
  private $skip_filters   = array();
  
  
  protected function skip_filter($filter)
  {
    $filters = func_get_args();
    $this->skip_filters = array_merge($this->skip_filters, $filters);
  }
  
  
  # Alias for +append_before_filter+.
  protected function before_filter($filter)
  {
    $filters = func_get_args();
    call_user_func_array(array($this, 'append_before_filter'), $filters);
  }
  
  protected function append_before_filter($filter)
  {
    $filters = func_get_args();
    $this->_append_filters('before', $filters);
  }
  
  protected function prepend_before_filter($filter)
  {
    $filters = func_get_args();
    $this->_prepend_filters('before', $filters);
  }
  
  
  # Alias for +append_after_filter+.
  protected function after_filter($filter)
  {
    $filters = func_get_args();
    call_user_func_array(array($this, 'append_after_filter'), $filters);
  }
  
  protected function append_after_filter($filter)
  {
    $filters = func_get_args();
    $this->_append_filters('after', $filters);
  }
  
  protected function prepend_after_filter($filter)
  {
    $filters = func_get_args();
    $this->_prepend_filters('after', $filters);
  }
  
  
  private function _prepend_filters($to, $filters)
  {
    $to = "{$to}_filters";
    
    $options = end($filters);
    if (is_array($options)) {
      unset($filters[key($filters)]);
    }
    else {
      $options = null;
    }
    
    array_reverse($filters);
    foreach($filters as $filter) {
      array_unshift($this->$to, array($filter, $options));
    }
  }
  
  private function _append_filters($to, $filters)
  {
    $to = "{$to}_filters";
    
    $options = end($filters);
    if (is_array($options)) {
      unset($filters[key($filters)]);
    }
    else {
      $options = null;
    }
    
    foreach($filters as $filter) {
      array_push($this->$to, array($filter, $options));
    }
  }
  
  
  # @private
  protected function process_before_filters()
  {
    foreach($this->before_filters as $filter)
    {
      if (!in_array($filter[0], $this->skip_filters)
        and (!isset($filter[1]['except']) or !in_array($this->action, $filter[1]['except']))
        and (!isset($filter[1]['only'])   or  in_array($this->action, $filter[1]['only'])))
      {
        $rs = $this->{$filter[0]}();
        
        if ($rs === false) {
          throw new ActionController_FailedFilter();
        }
      }
    }
  }
  
  # @private
  protected function process_after_filters()
  {
    foreach($this->after_filters as $filter)
    {
      if (!in_array($filter[0], $this->skip_filters)
        and (!isset($filter[1]['except']) or !in_array($this->action, $filter[1]['except']))
        and (!isset($filter[1]['only'])   or  in_array($this->action, $filter[1]['only'])))
      {
        $rs = $this->{$filter[0]}();
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
