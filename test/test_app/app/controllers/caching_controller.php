<?php

class CachingController extends ApplicationController
{
  protected $caches_page = array(
    'index',
    'show' => array('unless' => array(':format' => 'html')),
    'feed' => array('if'     => array(':format' => 'xml'))
  );
  
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
}

?>
