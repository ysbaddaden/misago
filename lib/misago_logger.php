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
    }
  }
  
  # Logs an error.
  function error($message)
  {
    if ($this->level <= self::ERROR) {
      $this->add(self::ERROR, $message);
    }
  }
  
  # Logs an warning.
  function warn($message)
  {
    if ($this->level <= self::WARN) {
      $this->add(self::WARN, $message);
    }
  }
  
  # logs a notice.
  function notice($message)
  {
    if ($this->level <= self::NOTICE) {
      $this->add(self::NOTICE, $message);
    }
  }
  
  # logs an information message.
  function info($message)
  {
    if ($this->level <= self::INFO) {
      $this->add(self::INFO, $message);
    }
  }
  
  # Logs a debug message.
  function debug($message)
  {
    if ($this->level <= self::DEBUG) {
      $this->add(self::DEBUG, $message);
    }
  }
  
  # Logs an unknown error.
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

class MisagoLogger extends Logger
{
  static private $singleton;
  public $auto_flush = true;
  
  function __construct()
  {
    Logger::__construct(ROOT."/log/{$_SERVER['MISAGO_ENV']}.log");
    switch(cfg::get('log_level', DEBUG ? 'debug' : 'info'))
    {
      case 'error': $this->level = Logger::ERROR; break;
      case 'warn':  $this->level = Logger::WARN;  break;
      case 'info':  $this->level = Logger::INFO;  break;
      case 'debug': $this->level = Logger::DEBUG; break;
    }
  }
  
  static function singleton()
  {
    if (!isset(self::$singleton)) {
      self::$singleton = new self();
    }
    return self::$singleton;
  }
}

?>
