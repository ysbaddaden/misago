<?php

class MisagoMemcache extends Memcache
{
  protected $permanent = false;
  protected $host      = 'localhost';
  protected $port      = 11200;
  protected $timeout   = 1;
  
  function __construct()
  {
    if (cfg::isset('memcache_permanent')) {
      $this->permanent = cfg::get('memcache_permanent');
    }
    if (cfg::isset('memcache_host')) {
      $this->host = cfg::get('memcache_host');
    }
    if (cfg::isset('memcache_port')) {
      $this->port = cfg::get('memcache_port');
    }
    if (cfg::isset('memcache_timeout')) {
      $this->timeout = cfg::get('memcache_timeout');
    }
  }
  
  function connect()
  {
    parent::connect($this->host, $this->port, $this->timeout);
  }
  
  function pconnect()
  {
    parent::pconnect($this->host, $this->port, $this->timeout);
  }
}

?>
