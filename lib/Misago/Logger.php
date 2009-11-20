<?php
namespace Misago;

class Logger extends \Logger
{
  static private $singleton;
  public $auto_flush = true;
  
  function __construct()
  {
    parent::__construct(ROOT."/log/{$_SERVER['MISAGO_ENV']}.log");
    switch(cfg_get('log_level', ($_SERVER['MISAGO_ENV'] == 'production') ? 'info' : 'debug'))
    {
      case 'error': $this->level = static::ERROR; break;
      case 'warn':  $this->level = static::WARN;  break;
      case 'info':  $this->level = static::INFO;  break;
      case 'debug': $this->level = static::DEBUG; break;
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
