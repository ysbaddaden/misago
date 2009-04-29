<?php
/**
 * 
 * @package ActionView
 * @subpackage Builder
 */
class ActionView_Builder extends ActionView_Base
{
  protected $format;
  protected $builder;
  
  function __construct($format='xml')
  {
    $this->format = $format;
    
    $class = "ActionView_Builders_".String::camelize($format)."Builder";
    $this->builder = new $class();
  }
  
  function render($data)
  {
    return $this->builder->render($data);
  }
}

?>
