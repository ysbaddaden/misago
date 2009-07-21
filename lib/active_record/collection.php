<?php

# TODO: Write tests!
# IMPROVE: Check wether parent is a new_record or not (before saving).
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
  
  function find($args)
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
    
    $options = $this->klass->merge_options($options,
      array('conditions' => $this->options['find_options']));
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
    if ($this->parent->new_record)
    {
      if (!$record->save()) {
        return false;
      }
    }
    return $record;
  }
  
  # Deletes the given records. They are removed from the collection, too.
  function delete($record)
  {
    $records = func_get_args();
    $removed = array();
    
    # deletion from database
    $this->klass->transaction('begin');
    foreach($this as $i => $record)
    {
      if (in_array($record, $records))
      {
        if (!$record->delete())
        {
          $this->klass->transaction('rollback');
          return false;
        }
        $removed[] = $i;
      }
    }
    $this->klass->transaction('commit');
    
    # removes from collection
    foreach($removed as $i) {
      $this->offsetUnset($i);
    }
    return true;
  }
  
  # Deletes all records. They're removed from the collection, too.
  function delete_all()
  {
    # deletion from database
    $this->klass->transaction('begin');
    foreach($this as $record)
    {
      if (!$this->record)
      {
        $this->klass->transaction('rollback');
        return false;
      }
    }
    $this->klass->transaction('commit');
    
    # clears collection
    $this->clear();
    return true;
  }
  
  # Destroys all records. They're removed from the collection, too.
  function destroy_all()
  {
    $records = func_get_args();
    
    # deletion from database
    $this->klass->transaction('begin');
    foreach($this as $i => $record)
    {
      if (!$record->destroy())
      {
        $this->klass->transaction('rollback');
        return false;
      }
    }
    $this->klass->transaction('end');
    
    # clears collection
    $this->clear();
    return true;
  }
  
  # Clears the collection.
  function clear()
  {
    $this->exchangeArray(array());
  }
}

?>
