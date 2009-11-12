<?php

class ActiveSupport_Datetime extends DateTime
{
  protected $original_time;
  
  # Works like +DateTime::__construct+, but allows +$timezone+ to be
  # a string (the +DateTimeZone+ object will be created automatically).
  function __construct($time='now', $timezone=null)
  {
    if ($time === '') {
      $this->original_time = '';
    }
    else
    {
      try
      {
        if ($timezone !== null)
        {
          if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
          }
          parent::__construct($time, $timezone);
        }
        else {
          parent::__construct($time);
        }
      }
      catch(Exception $e) {
        $this->original_time = $time;
      }
    }
  }
  
  function __call($method, $args)
  {
    switch($method)
    {
      case 'getTimestamp': return $this->format('U');
    }
  }
  
  # Returns the distance between 2 dates as seconds.
  function distance($datetime)
  {
    if (!$datetime instanceof DateTime) $datetime = new DateTime($datetime);
    return $datetime->to_format('U') - $this->to_format('U');
  }
  
  function is_valid()
  {
    return ($this->original_time === null);
  }
  
  # RSS date format (RFC2822).
  function to_rfc2822()
  {
    return $this->format(DateTime::RFC2822);
  }
  
  # ATOM date format (ISO8601).
  function to_iso8601()
  {
    return $this->format(DateTime::ISO8601);
  }
  
  function __toString()
  {
    return ($this->original_time === null) ? $this->format('Y-m-d H:i:s') : $this->original_time;
  }
  
  # Same as <tt>__toString</tt>, but permits to return a number
  # instead of SQL formatted date.
  # 
  #   $date = new ActiveSupport_Datetime('2009-09-23 14:32:59');
  #   $date->to_s()         # => '2009-09-23 14:32:59'
  #   $date->to_s('number') # => '20090923143259'
  # 
  function to_s($format=null)
  {
    switch ($format)
    {
      case 'number': return preg_replace('/[^0-9]/', '', $this->__toString());
      default: return $this->__toString();
    }
  }
  
  # TODO: ActiveSupport_Datetime::ago().
  function ago()
  {
    
  }
}

class ActiveSupport_Time extends ActiveSupport_Datetime
{
  protected $type = 'time';
  
  function __toString()
  {
    return $this->format('H:i:s');
  }
}

class ActiveSupport_Date extends ActiveSupport_Datetime
{
  protected $type = 'date';
  
  function __toString()
  {
    return $this->format('Y-m-d');
  }
}

?>
