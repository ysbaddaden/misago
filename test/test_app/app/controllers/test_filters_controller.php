<?php

class TestFiltersController extends ActionController_Base
{
  public $var_a;
  public $var_c;
  public $var_d;
  public $var_e;
  public $var_index;
  
  function __construct($type)
  {
    ActionController_Base::__construct();
    
    switch($type)
    {
      case 'before':
        $this->append_before_filter('b', 'c');
        $this->prepend_before_filter('a');
      break;
      
      case 'after':
        $this->append_after_filter('b', 'c');
        $this->prepend_before_filter('a');
      break;
      
      case 'skip':
        $this->append_before_filter('a', 'b', 'c');
        $this->append_after_filter('d', 'e');
        $this->skip_filter('b', 'e');
      break;
    }
  }
  
  function index()
  {
    $this->var_index = true;
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
