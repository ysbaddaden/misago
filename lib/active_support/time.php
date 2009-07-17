<?php
# @namespace ActiveSupport
class Time extends Object
{
  protected $raw_time;
  protected $timestamp;
  protected $type;
  
  function __construct($time=null, $type='datetime')
  {
    $this->raw_time  = $time;
    $this->timestamp = is_string($time) ? strtotime($time) : $time;
    $this->type = $type;
  }
  
  function is_past()
  {
    $today = strtotime(date('Y-m-d'));
    return ($this->timestamp < $today);
  }
  
  function is_yesterday()
  {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $date      = date('Y-m-d', $this->timestamp);
    return ($date == $yesterday);
  }
  
  function is_today()
  {
    $today = date('Y-m-d');
    $date  = date('Y-m-d', $this->timestamp);
    return ($date == $today);
  }
  
  function is_tomorrow()
  {
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $date     = date('Y-m-d', $this->timestamp);
    return ($date == $tomorrow);
  }
  
  function is_future()
  {
    $today = strtotime(date('Y-m-d'));
    return ($this->timestamp > $today);
  }
  
  function is_this_year()
  {
    return (date('Y', $this->timestamp) == date('Y'));
  }
  
  function __get($attr)
  {
    switch($attr)
    {
      case 'year':  return date('Y', $this->timestamp); break;
      case 'month': return date('m', $this->timestamp); break;
      case 'day':   return date('d', $this->timestamp); break;
      case 'hour':  return date('H', $this->timestamp); break;
      case 'min':   return date('i', $this->timestamp); break;
      case 'sec':   return date('s', $this->timestamp); break;
    }
    trigger_error("Unknown attribute: Time::$attr", E_USER_WARNING);
  }
  
  function __toString()
  {
    if ($this->timestamp === false) {
      return $this->raw_time;
    }
    switch($this->type)
    {
      case 'datetime': return date('Y-m-d H:i:s', $this->timestamp);
      case 'date':     return date('Y-m-d', $this->timestamp);
      case 'time':     return date('H:i:s', $this->timestamp);
    }
    /*
    switch($this->type)
    {
      case 'datetime': return strftime('%m/%d/%Y %I:%M:%S%P', $this->timestamp);
      case 'date':     return strftime('%m/%d/%Y', $this->timestamp);
      case 'time':     return strftime('%I:%M:%S%P', $this->timestamp);
    }
    */
#    return $this->to_query();
  }
  /*
  function to_query($type=null)
  {
    if ($type === null) {
      $type = $this->type;
    }
    switch($this->type)
    {
      case 'datetime': return date('Y-m-d H:i:s', $this->timestamp);
      case 'date':     return date('Y-m-d', $this->timestamp);
      case 'time':     return date('H:i:s', $this->timestamp);
    }
  }
  
  function to_s($type=null)
  {
    if ($type === null) {
      $type = $this->type;
    }
    switch($type)
    {
      case 'datetime': return strftime($this->is_this_year() ? '%b %e, %I:%M%P' : '%b %e %Y, %I:%M%P', $this->timestamp);
      case 'date':     return strftime($this->is_this_year() ? '%b %e' : '%b %e %Y', $this->timestamp);
      case 'time':     return strftime('%I:%M%P', $this->timestamp);
      case 'db':       return $this->to_query();
    }
  }
  */
  function to_timestamp()
  {
    return $this->timestamp;
  }
  
  # RSS date format (RFC2822)
  function to_rfc2822()
  {
    return date('r', $this->timestamp);
  }
  
  # ATOM date format (ISO8601)
  function to_iso8601()
  {
    return date('c', $this->timestamp);
  }
  
  function ago()
  {
    $diff     = time() - $this->timestamp;
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
