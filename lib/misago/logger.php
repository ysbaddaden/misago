<?php

class Misago_Logger extends Logger
{
  static private $singleton;
  public $auto_flush = true;
  
  function __construct()
  {
    Logger::__construct(ROOT."/log/{$_SERVER['MISAGO_ENV']}.log");
    switch(cfg::get('log_level', ($_SERVER['MISAGO_ENV'] == 'production') ? 'info' : 'debug'))
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
