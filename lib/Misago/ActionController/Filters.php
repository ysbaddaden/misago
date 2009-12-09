<?php
namespace Misago\ActionController;

# Filters permit to handle a request before and after executing an action.
# 
# Example: 
# 
#   class PostsController extends ApplicationController
#   {
#     static __constructStatic()
#     {
#       static::before_filter('authenticate');
#       static::before_filter('requires_login', array('except' => array('index', 'show')));
#       static::after_filter('mark_searched_tokens', 'compress_response', array('only' => array('index')));
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
  static private $before_filters = array();
  static private $after_filters  = array();
  static private $skip_filters   = array();
  
  
  static protected function skip_filter($filter)
  {
    $filters = func_get_args();
    
    if (isset(self::$skip_filters[get_called_class()]))
    {
      self::$skip_filters[get_called_class()] =
        array_merge(self::$skip_filters[get_called_class()], $filters);
    }
    else {
      self::$skip_filters[get_called_class()] = $filters;
    }
  }
  
  
  # Alias for <tt>append_before_filter</tt>.
  static protected function before_filter($filter, $options=null)
  {
    $filters = func_get_args();
    self::_append_filters('before', $filters);
  }
  
  static protected function append_before_filter($filter, $options=null)
  {
    $filters = func_get_args();
    self::_append_filters('before', $filters);
  }
  
  static protected function prepend_before_filter($filter, $options=null)
  {
    $filters = func_get_args();
    self::_prepend_filters('before', $filters);
  }
  
  
  # Alias for <tt>append_after_filter</tt>.
  static protected function after_filter($filter, $options=null)
  {
    $filters = func_get_args();
    self::_append_filters('after', $filters);
  }
  
  static protected function append_after_filter($filter, $options=null)
  {
    $filters = func_get_args();
    self::_append_filters('after', $filters);
  }
  
  protected function prepend_after_filter($filter, $options=null)
  {
    $filters = func_get_args();
    self::_prepend_filters('after', $filters);
  }
  
  
  static private function _prepend_filters($to, $filters)
  {
    $to = "{$to}_filters";
    $to =& self::$$to;
    
    $options = end($filters);
    if (is_array($options)) {
      unset($filters[key($filters)]);
    }
    else {
      $options = null;
    }
    
    if (!isset($to[get_called_class()])) {
      $to[get_called_class()] = array();
    }
    
    $filters = array_reverse($filters);
    foreach($filters as $filter) {
      array_unshift($to[get_called_class()], array($filter, $options));
    }
  }
  
  static private function _append_filters($to, $filters)
  {
    $to = "{$to}_filters";
    $to =& self::$$to;
    
    $options = end($filters);
    if (is_array($options)) {
      unset($filters[key($filters)]);
    }
    else {
      $options = null;
    }
    
    if (!isset($to[get_called_class()])) {
      $to[get_called_class()] = array();
    }
    
    foreach($filters as $filter) {
      array_push($to[get_called_class()], array($filter, $options));
    }
  }
  
  
  # :nodoc:
  protected function process_before_filters()
  {
    if (empty(self::$before_filters[get_called_class()])) {
      return;
    }
    foreach(self::$before_filters[get_called_class()] as $filter)
    {
      if ((!isset(self::$skip_filters[get_called_class()]) or !in_array($filter[0], self::$skip_filters[get_called_class()]))
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
  
  # :nodoc:
  protected function process_after_filters()
  {
    if (empty(self::$after_filters[get_called_class()])) {
      return;
    }
    foreach(self::$after_filters[get_called_class()] as $filter)
    {
      if ((!isset(self::$skip_filters[get_called_class()]) or !in_array($filter[0], self::$skip_filters[get_called_class()]))
        and (!isset($filter[1]['except']) or !in_array($this->action, $filter[1]['except']))
        and (!isset($filter[1]['only'])   or  in_array($this->action, $filter[1]['only'])))
      {
        $rs = $this->{$filter[0]}();
      }
    }
  }
}

# :nodoc:
class FailedFilter extends \Exception
{
  function __construct() {
    parent::__construct('', 0);
  }
}

?>
