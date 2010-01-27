<?php

class CachingController extends ApplicationController
{
  static function __constructStatic()
  {
    parent::__constructStatic();
    
    static::caches_page('index');
    static::caches_page('show', array('unless' => array(':format' => 'html')));
    static::caches_page('feed', array('if'     => array(':format' => 'xml')));
  }
  
  function index()
  {
    $this->head(200);
  }
  
  function show()
  {
    $this->head(200);
  }
  
  function feed()
  {
    $this->head(200);
  }
  
  function error()
  {
    throw new \Misago\Exception('An error occured', 500);
  }
}

?>
