<?php

# TODO: Tests!
class ActiveRecord_Collection extends ActiveArray
{
  protected $parent;
  protected $options;
  
  function __construct($parent, $childs, $options)
  {
    $this->parent  = $parent;
    $this->options =& $options;
    parent::__construct($childs, $this->options['class_name']);
  }
  
  function build($attributes)
  {
    $class = $this->model;
    $attributes[$this->options['foreign_key']] = $this->parent->{$this->options['foreign_key']};
    return new $class($this->model);
  }
  
  function create($attributes)
  {
    $class  = $this->model;
    $record = new $class();
    $attributes[$this->options['foreign_key']] = $this->parent->{$this->options['foreign_key']};
    return $record->create(&$attributes);
  }
  
  /*
  function find($args)
  {
    $class = $this->model;
    $args  = func_get_args();
    return call_user_func_array(array(new $class(), 'find'), &$args);
  }
  
  function delete($record)
  {
    $class = $this->model;
    $args  = func_get_args();
    foreach($args as $record) {
      $record->delete();
    }
  }
  
  function delete_all()
  {
    foreach($this as $record) {
      $record->delete();
    }
  }
  */
}

?>
