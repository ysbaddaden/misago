<?php

class TestBeforeFiltersController extends TestFiltersController
{
  static function __constructStatic()
  {
    static::append_before_filter('b', 'c');
    static::prepend_before_filter('a', array('except' => array('show', 'neo')));
  }
}
TestBeforeFiltersController::__constructStatic();

class TestAfterFiltersController extends TestFiltersController
{
  static function __constructStatic()
  {
    static::append_after_filter('b', 'c');
    static::prepend_before_filter('a', array('only' => array('index')));
  }
}
TestAfterFiltersController::__constructStatic();

class TestSkipFiltersController extends TestFiltersController
{
  static function __constructStatic()
  {
    static::append_before_filter('a', 'b', 'c');
    static::append_after_filter('d', 'e');
    static::skip_filter('b', 'e');
  }
}
TestSkipFiltersController::__constructStatic();

class TestFiltersController extends ApplicationController
{
  public $var_a;
  public $var_c;
  public $var_d;
  public $var_e;
  public $var_action;
  
  function index()
  {
    $this->var_action = 'index';
    $this->head(200);
  }
  
  function show()
  {
    $this->var_action = 'show';
    $this->head(200);
  }
  
  function neo()
  {
    $this->var_action = 'neo';
    $this->head(200);
  }
  
  protected function a()
  {
    $this->var_a = 'a';
  }
  
  protected function b()
  {
    return false;
  }
  
  protected function c()
  {
    $this->var_c = 'c';
  }
  
  protected function d()
  {
    $this->var_d = 'd';
  }
  
  protected function e()
  {
    $this->var_e = 'd';
  }
}

?>
