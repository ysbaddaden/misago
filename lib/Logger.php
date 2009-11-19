<?php

class Logger
{
  const ERROR  = 5;
  const WARN   = 4;
  const NOTICE = 3;
  const INFO   = 2;
  const DEBUG  = 1;
  
  public  $level      = 1;
  public  $auto_flush = false;
  
  private $devices    = array();
  private $messages   = array();
  
  function __construct($log_device=STDERR)
  {
    $this->add_log_device($log_device);
    
    if (PHP_SAPI != 'cli'
      and file_exists(TMP."/msgqueue-{$_SERVER['MISAGO_ENV']}"))
    {
      $this->msg_queue = msg_get_queue(ftok(TMP."/msgqueue-{$_SERVER['MISAGO_ENV']}", 'M'), 0666);
    }
  }
  
  function __destruct()
  {
    $this->flush();
  }
  
  # Adds a file handler or file to write to.
  # You may filter severity per device too. You may, for instance, want
  # to log all messages to a log file, while outputting only notices,
  # warnings and errors to STDERR.
  # 
  #   $logger->add_log_device('log/development.log');
  #   $logger->add_log_device(STDERR, Logger::WARN);
  # 
  function add_log_device($device, $level=null)
  {
    $file = is_string($device) ? fopen($device, 'a') : $device;
    $this->devices[] = array('file' => $file, 'log_level' => $level);
  }
  
  # Logs a message.
  function add($severity, $message)
  {
    $this->messages[$severity][] = $this->format_message($severity, $message);
    $this->auto_flush && $this->flush();
  }
  
  # Manually flushes messages on log devices.
  function flush()
  {
    foreach($this->messages as $severity => $messages)
    {
      $messages = implode("\n", $messages);
      unset($this->messages[$severity]);
      
      foreach($this->devices as $device)
      {
        if ($device['log_level'] !== null and $device['log_level'] > $severity) continue;
        fwrite($device['file'], $messages);
      }
      
      if (isset($this->msg_queue)) {
        msg_send($this->msg_queue, $severity, $messages, false, true, $errno);
      }
    }
  }
  
  function log_error()
  {
    return ($this->level <= self::ERROR);
  }
  
  function log_warning()
  {
    return ($this->level <= self::WARNING);
  }
  
  function log_notice()
  {
    return ($this->level <= self::NOTICE);
  }
  
  function log_info()
  {
    return ($this->level <= self::INFO);
  }
  
  function log_debug()
  {
    return ($this->level <= self::DEBUG);
  }
  
  # Logs an error.
  function error($message)
  {
    $this->log_error() && $this->add(self::ERROR, $message);
  }
  
  # Logs a warning.
  function warn($message)
  {
    $this->log_warning() && $this->add(self::WARN, $message);
  }
  
  # logs a notice.
  function notice($message)
  {
    $this->log_notice() && $this->add(self::NOTICE, $message);
  }
  
  # logs an information message.
  function info($message)
  {
    $this->log_info() && $this->add(self::INFO, $message);
  }
  
  # Logs a debug message.
  function debug($message)
  {
    $this->log_debug() && $this->add(self::DEBUG, $message);
  }
  
  # Logs an unknown error. Unknown errors will always be logged.
  function unknown($message)
  {
    $this->add(null, $message);
  }
  
  protected function format_message($severity, $message)
  {
    return "$message\n";
    /*
    switch($severity)
    {
      case Logger::ERROR:  $severity = "ERROR"; break;
      case Logger::WARN:   $severity = "WARN"; break;
      case Logger::NOTICE: $severity = "NOTICE"; break;
      case Logger::INFO:   $severity = "INFO"; break;
      case Logger::DEBUG:  $severity = "DEBUG"; break;
      default: $severity = 'UNKNOWN';
    }
    return sprintf("[%s] %s -- %s\n", date('Y-m-d H:i:s'), $severity, $message);
    */
  }
}

?>
