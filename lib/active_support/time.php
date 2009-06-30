<?php
/**
 * 
 * @package ActiveSupport
 */
class Time extends Object
{
  protected $_time;
  protected $type;
  
  function __construct($time=null, $type='datetime')
  {
    $this->time = ($time === null) ? time() : $time;
    $this->type = $type;
  }
  
  function __get($var)
  {
    if ($var == 'time') {
      return $this->_time;
    }
    throw new Exception("Unknown attribute $var");
  }
  
  function __set($var, $value)
  {
    if ($var == 'time') {
      return $this->_time = is_string($value) ? strtotime($value) : $value;
    }
    throw new Exception("Unknown attribute $var");
  }
  
  function __isset($var)
  {
    $var = "_$var";
    return isset($this->$var);
  }
  
  
  function is_past()
  {
    $today = strtotime(date('Y-m-d'));
    return ($this->_time < $today);
  }
  
  function is_yesterday()
  {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $date      = date('Y-m-d', $this->_time);
    return ($date == $yesterday);
  }
  
  function is_today()
  {
    $today = date('Y-m-d');
    $date  = date('Y-m-d', $this->_time);
    return ($date == $today);
  }
  
  function is_tomorrow()
  {
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $date     = date('Y-m-d', $this->_time);
    return ($date == $tomorrow);
  }
  
  function is_future()
  {
    $today = strtotime(date('Y-m-d'));
    return ($this->_time > $today);
  }
  
  function is_this_year()
  {
    return (date('Y', $this->_time) == date('Y'));
  }
  
  
  function __toString()
  {
    switch($this->type)
    {
      case 'datetime': return strftime('%m/%d/%Y %I:%M:%S%P', $this->_time);
      case 'date':     return strftime('%m/%d/%Y', $this->_time);
      case 'time':     return strftime('%I:%M:%S%P', $this->_time);
    }
  }
  
  function to_query($type=null)
  {
    if ($type === null) {
      $type = $this->type;
    }
    switch($this->type)
    {
      case 'datetime': return date('Y-m-d H:i:s', $this->_time);
      case 'date':     return date('Y-m-d', $this->_time);
      case 'time':     return date('H:i:s', $this->_time);
    }
  }
  
  function to_s($type=null)
  {
    if ($type === null) {
      $type = $this->type;
    }
    switch($type)
    {
      case 'datetime': return strftime($this->is_this_year() ? '%b %e, %I:%M%P' : '%b %e %Y, %I:%M%P', $this->_time);
      case 'date':     return strftime($this->is_this_year() ? '%b %e' : '%b %e %Y', $this->_time);
      case 'time':     return strftime('%I:%M%P', $this->_time);
      case 'db':       return $this->to_query();
    }
  }
  
  function to_timestamp()
  {
    return $this->_time;
  }
  
  # There is no XML format, but it's used by some to_xml() methods.
  function to_xml()
  {
    return $this->to_query();
  }
  
  
  # RSS date format (RFC2822)
  function to_rfc2822()
  {
    return date('r', $this->_time);
  }
  
  # ATOM date format (ISO8601)
  function to_iso8601()
  {
    return date('c', $this->_time);
  }
  
  function ago()
  {
    $diff     = time() - $this->_time;
    $day_diff = round($diff / 86400);
    
    # in future
    if ($day_diff < 0 or $diff < 0)
    {
      # today
      if ($day_diff == 0)
      {
        if ($diff <= 60) {
          return 'just now';
        }
        elseif ($diff <= 120) {
          return 'in a minute';
        }
        elseif ($diff <= 3600) {
          return sprintf('in %d minutes', round($diff / 60));
        }
        elseif ($diff <= 7200) {
          return 'in an hour';
        }
        elseif ($diff <= 86400) {
          return sprintf('in %d hours', round($diff / 3600));
        }
      }
      
      # future
      elseif ($day_diff == 1) {
        return 'tomorrow';
      }
      elseif ($day_diff <= 7) {
        return sprintf('in %d days', $day_diff);
      }
      elseif ($day_diff <= 31)
      {
        $week_diff = round($day_diff / 7);
        if ($week_diff == 1) {
          return 'next week';
        }
        return sprintf('in %d weeks', $week_diff);
      }
    }
    
    # today
    elseif ($day_diff == 0)
    {
      if ($diff <= 60) {
        return 'just now';
      }
      elseif ($diff <= 120) {
        return 'a minute ago';
      }
      elseif ($diff <= 3600) {
        return sprintf('%d minutes ago', round($diff / 60));
      }
      elseif ($diff <= 7200) {
        return 'an hour ago';
      }
      elseif ($diff <= 86400) {
        return sprintf('%d hours ago', round($diff / 3600));
      }
    }
    
    # in the past
    elseif ($day_diff == 1) {
     return 'yesterday';
    }
    elseif ($day_diff <= 7) {
      return sprintf('%d days ago', $day_diff);
    }
    elseif ($day_diff <= 31)
    {
      $week_diff = round($day_diff / 7);
      if ($week_diff == 1) {
        return 'last week';
      }
      return sprintf('%d weeks ago', $week_diff);
    }
    
    # too old / far
    return $this->to_nice();
  }
}

?>
