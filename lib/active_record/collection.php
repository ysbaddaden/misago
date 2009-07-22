<?php

class ActiveRecord_Collection extends ActiveArray
{
  protected $parent;
  protected $options;
  
  function __construct($parent, $childs, $options)
  {
    $this->parent  =  $parent;
    $this->options =& $options;
    parent::__construct($childs, $this->options['class_name']);
  }
  
  function find()
  {
    $args = func_get_args();
    
    # extracts options from args
    foreach(array_keys($args) as $i)
    {
      if (is_array($args[$i]))
      {
        $options =& $args[$i];
        break;
      }
    }
    if (!isset($options))
    {
      $options = array();
      $args[] =& $options;
    }
    
	  $_options = isset($assoc['find_options']) ? $this->options['find_options'] : array();
	  $_options['conditions'] = array($this->options['find_key'] => $this->parent->id);
    $options = $this->klass->merge_options($options, $_options);
    return call_user_func_array(array($this->klass, 'find'), &$args);
  }
  
  # Adds a new record to the collection, but doesn't save it.
  function build($attributes)
  {
    $attributes[$this->options['foreign_key']] = $this->parent->id;
    
    $class  = $this->model;
    $record = new $class($attributes);
    
    $this->offsetSet(null, $record);
    return $record;
  }
  
  # Adds a new record to the collection, and saves it.
  function create($attributes)
  {
    $record = $this->build($attributes);
    if (!$this->parent->new_record and !$record->save()) {
      return false;
    }
    return $record;
  }
  
  # Deletes the given records. They are removed from the collection, too.
  function delete($record)
  {
    $records = func_get_args();
    $removed = $this->klass->transaction(array($this, '_block_delete'), $records);
    if ($removed === false) {
      return false;
    }
    
    # removes deleted records from collection
    foreach($removed as $i) {
      $this->offsetUnset($i);
    }
    return true;
  }
  
  # Deletes all records. They're removed from the collection, too.
  function delete_all()
  {
    $this->klass->transaction(array($this, '_block_delete_all'));
    $this->clear();
    return true;
  }
  
  # Destroys all records. They're removed from the collection, too.
  function destroy_all()
  {
    $this->klass->transaction(array($this, '_block_destroy_all'));
    $this->clear();
    return true;
  }
  
  # Clears the collection.
  function clear()
  {
    $this->exchangeArray(array());
  }
  
  
  # @private
  function _block_delete()
  {
    $records = func_get_args();
    $removed = array();
    foreach($this as $i => $record)
    {
      if (in_array($record, $records))
      {
        if (!$record->new_record)
        {
          $record->do_delete();
          $removed[] = $i;
        }
      }
    }
    return $removed;
  }
  
  # @private
  function _block_delete_all()
  {
    foreach($this as $record)
    {
      if (!$record->new_record) {
        $record->do_delete();
      }
    }
  }
  
  # @private
  function _block_destroy_all()
  {
    foreach($this as $record)
    {
      if (!$record->new_record) {
        $record->do_destroy();
      }
    }
  }
}

?>
