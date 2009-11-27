<?php
namespace Misago\ActionController;

# Filters permit to handle a request before and after executing an action.
# 
# Example: 
# 
#   class PostsController extends ApplicationController
#   {
#     protected __construct()
#     {
#       parent::__construct();
#       $this->before_filter('authenticate');
#       $this->before_filter('requires_login', array('except' => array('index', 'show')));
#       $this->after_filter('mark_searched_tokens', 'compress_response', array('only' => array('index')));
#     }
#   }
# 
# =Before filters
# 
# Before filters are processed before processing the action. If a +before_filter+
# method returns false, the filters' chain will stop and the action won't be processed.
# This is useful for handling pages where the user must be authenticated, for instance.
# 
# = After filters
# 
# After filters are run 
# 
# = Skip filters
# 
# After filters are run 
# 
# = Options:
# 
# All filters have the following options:
# 
# - +only+   - a list of actions that must process the filter.
# - +except+ - a list of actions where not to process the filter (others will process the filter).
# 
abstract class Filters extends Rescue
{
  private $before_filters = array();
  private $after_filters  = array();
  private $skip_filters   = array();
  
  
  protected function skip_filter($filter)
  {
    $filters = func_get_args();
    $this->skip_filters = array_merge($this->skip_filters, $filters);
  }
  
  
  # Alias for <tt>append_before_filter</tt>.
  protected function before_filter($filter, $options=null)
  {
    $filters = func_get_args();
    call_user_func_array(array($this, 'append_before_filter'), $filters);
  }
  
  protected function append_before_filter($filter, $options=null)
  {
    $filters = func_get_args();
    $this->_append_filters('before', $filters);
  }
  
  protected function prepend_before_filter($filter, $options=null)
  {
    $filters = func_get_args();
    $this->_prepend_filters('before', $filters);
  }
  
  
  # Alias for <tt>append_after_filter</tt>.
  protected function after_filter($filter, $options=null)
  {
    $filters = func_get_args();
    call_user_func_array(array($this, 'append_after_filter'), $filters);
  }
  
  protected function append_after_filter($filter, $options=null)
  {
    $filters = func_get_args();
    $this->_append_filters('after', $filters);
  }
  
  protected function prepend_after_filter($filter, $options=null)
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
    
    $filters = array_reverse($filters);
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
  
  
  # :private:
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
          throw new FailedFilter();
        }
      }
    }
  }
  
  # :private:
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

# :private:
class FailedFilter extends \Exception
{
  function __construct() {
    parent::__construct('', 0);
  }
}

?>