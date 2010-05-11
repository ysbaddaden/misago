<?php
namespace Misago\ActiveSupport;

# FIXME: Misago should use UTC dates internally, and return UTC dates with their offset. (ie. Fri, 31 Dec 1999 14:00:00 HST -10:00)
# TODO: local(): returns the UTC date with the default timezone's offset (ie. Mon, 21 Feb 2005 10:11:12 -0600).
# TODO: utc(): returns the UTC date with offset +0000 (ie. ).
# TODO: in_time_zone($tz): returns the UTC date with zone's offset (ie. Mon, 21 Feb 2005 16:11:12 +0100).
class Datetime extends \DateTime
{
  protected $_string_format = 'Y-m-d H:i:s';
  protected $original_time  = null;
  
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
            $timezone = new \DateTimeZone($timezone);
          }
          parent::__construct($time, $timezone);
        }
        else {
          parent::__construct($time);
        }
      }
      catch(\Exception $e) {
        $this->original_time = $time;
      }
    }
  }
  
  function __call($method, $args)
  {
    switch($method)
    {
      case 'getTimestamp': return $this->format('U');
      default: trigger_error("Unknown method ActiveSupport\\".
        get_class($this)."::$method().", E_USER_ERROR);
    }
  }
  
  function __get($property)
  {
    switch($property)
    {
      case 'year':  return $this->format('Y'); break;
      case 'month': return $this->format('m'); break;
      case 'day':   return $this->format('d'); break;
      case 'hour':  return $this->format('H'); break;
      case 'min':   return $this->format('i'); break;
      case 'sec':   return $this->format('s'); break;
      default: trigger_error("Unknown property ActiveSupport\\".
        get_class($this)."::$property.", E_USER_WARNING);
    }
  }
  
  # Returns the distance between 2 dates as seconds.
  function distance($datetime)
  {
    if (!$datetime instanceof DateTime) $datetime = new DateTime($datetime);
    return $datetime->getTimestamp() - $this->getTimestamp();
  }
  
  function is_valid() {
    return ($this->original_time === null);
  }
  
  # RSS date format (RFC2822).
  function to_rfc2822() {
    return $this->format(DateTime::RFC2822);
  }
  
  # ATOM date format (ISO8601).
  function to_iso8601() {
    return $this->format(DateTime::ISO8601);
  }
  
  function __toString()
  {
    return ($this->original_time === null) ?
      (string)$this->format($this->_string_format) : (string)$this->original_time;
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
  
  function strftime($format) {
    return strftime($format, $this->getTimestamp());
  }
}

class Time extends Datetime {
  protected $_string_format = 'H:i:s';
}

class Date extends Datetime {
  protected $_string_format = 'Y-m-d';
}

?>
