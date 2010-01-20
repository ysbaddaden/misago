<?php
namespace Misago\ActiveRecord;
use Misago\ActiveSupport;

# Contains a collection of records.
# Used by +has_many+ and +HABTM+ relationships.
class Collection extends ActiveSupport\ActiveArray
{
  protected $parent;
  protected $options;
  private   $unloaded = true;
  
  function __construct($parent, $childs, $options)
  {
    $this->parent   = $parent;
    $this->options  = $options;
    $this->unloaded = ($childs === null);
    parent::__construct(($childs === null) ? array() : $childs, $this->options['class_name']);
  }
  
  function __get($attr)
  {
    if ($attr == 'options') {
      return $this->options;
    }
    elseif ($attr == 'parent') {
      return $this->parent;
    }
    return parent::__get($attr);
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
    return call_user_func_array(array($this->klass, 'find'), $args);
  }
  
  # Adds a new record to the collection, but doesn't save it.
  function build($attributes=array())
  {
    if (!isset($this->options['through'])) {
      $attributes[$this->options['foreign_key']] = $this->parent->id;
    }
    
    $class  = $this->model;
    $record = new $class($attributes);
    
    $this->offsetSet(null, $record);
    return $record;
  }
  
  # Adds a new record to the collection, and saves it.
  function create($attributes)
  {
    $record = $this->build($attributes);
    if (!$this->parent->new_record)
    {
      # record
      if (!$record->save()) {
        return false;
      }
      $this->create_join_relation($record);
    }
    return $record;
  }
  
  # Saves the collection.
  function save()
  {
    $self = $this;
    return $this->klass->transaction(function() use($self)
    {
      $fk = $self->options['foreign_key'];
      foreach($self as $record)
      {
        $record->{$fk} = $self->parent->id;
        $record->do_save();
        $self->create_join_relation($record);
      }
      return true;
    });
  }
  
  # :nodoc:
  function create_join_relation($record)
  {
    if (isset($this->options['through']))
    {
      $attributes = array(
        $this->options['foreign_key'] => $this->parent->id,
        $this->options['through_foreign_key'] => $record->id,
      );
      $this->parent->{$this->options['through']}->create($attributes);
    }
    elseif ($this->options['type'] == 'has_and_belongs_to_many')
    {
      $attributes = array(
        $this->options['foreign_key'] => $this->parent->id,
        $this->options['association_foreign_key'] => $record->id,
      );
      $this->parent->connection->insert($this->options['join_table'], $attributes);
    }
  }
  
  # Removes the given records from the collection by nullifying their
  # association. This does not destroy the objects.
  function delete($record)
  {
    $records = func_get_args();
    $self    = $this;
    
    return $this->klass->transaction(function() use($records, $self)
    {
      foreach($self->castArray() as $i => $record)
      {
        if (in_array($record, $records))
        {
          if (!$record->new_record) {
            $record->update_attribute($self->options['foreign_key'], null);
          }
          $self->offsetUnset($i);
        }
      }
      return true;
    });
  }
  
  # Destroys the given records (callbacks are processed).
  # They're removed from the collection too.
  function destroy($record)
  {
    $records = func_get_args();
    $self    = $this;
    
    return $this->klass->transaction(function() use($records, $self)
    {
      foreach($self->castArray() as $i => $record)
      {
        if (in_array($record, $records))
        {
          if (!$record->new_record) {
            $record->destroy();
          }
          $self->offsetUnset($i);
        }
      }
      return true;
    });
  }
  
  # Deletes all records (object callbacks aren't processed).
  # They're removed from the collection too.
  function delete_all()
  {
    $class_name = $this->options['class_name'];
    
    if ($this->options['type'] == 'has_and_belongs_to_many')
    {
      # deletions
      $conditions = "{$this->options['association_primary_key']} IN ( ".
        "SELECT {$this->options['association_foreign_key']} ".
        "FROM programmers_projects ".
        "WHERE ".$this->parent->connection->sanitize_sql_for_conditions(array(
          $this->options['foreign_key'] => $this->parent->id,
        ))." )";
      if ($class_name::delete_all($conditions) === false) {
        return false;
      }
      
      # cleanup
      $conditions = array($this->options['foreign_key'] => $this->parent->id);
      $this->parent->connection()->delete($this->options['join_table'], $conditions);
    }
    else
    {
      $conditions = array($this->options['foreign_key'] => $this->parent->id);
      if ($class_name::delete_all($conditions) === false) {
        return false;
      }
    }
    
    $this->clear();
    return true;
  }
  
  # Destroys all records (object callbacks are processed).
  # They're removed from the collection, too.
  function destroy_all()
  {
    $self = $this;
    $rs = $this->klass->transaction(function() use ($self)
    {
      foreach($self as $record)
      {
        if (!$record->new_record) {
          $record->do_destroy();
        }
      }
      return true;
    });
    
    if ($rs)
    {
      $this->clear();
      return true;
    }
    return false;
  }
  
  # Clears the collection. This doesn't delete the associated records.
  function clear() {
    $this->exchangeArray(array());
  }
  
  function size()
  {
    $options = $this->find_options();
    unset($options['select']);
    $class_name = $this->options['class_name'];
    return $class_name::count($options);
  }
  
  private function load()
  {
    if (!$this->parent->new_record
      and $this->count() == 0)
    {
      $options    = $this->find_options();
      $class_name = $this->options['class_name'];
      $childs     = $class_name::find($this->options['find_scope'], $options);
      
      if ($childs) {
        $this->exchangeArray($childs);
      }
      else {
        $this->clear();
      }
    }
    $this->unloaded = false;
  }
  
  private function & find_options()
  {
    $conditions = ($this->options['type'] == 'belongs_to') ?
      array($this->options['find_key'] => $this->parent->{$assoc['foreign_key']}) :
      array($this->options['find_key'] => $this->parent->id);
    
    $options = isset($this->options['find_options']) ?
      $this->options['find_options'] : array();
    $options['conditions'] = empty($options['conditions']) ? $conditions :
      $this->parent->merge_conditions($options['conditions'], $conditions);
    
    return $options;
  }
  
  
  function offsetExists($key)
  {
    $this->unloaded and $this->load();
    return parent::offsetExists($key);
  }
  
#  function offsetSet($key, $value)
#  {
#    $this->unloaded and $this->load();
#    return parent::offsetSet($key, $value);
#  }
  
  function offsetGet($key)
  {
    $this->unloaded and $this->load();
    return parent::offsetGet($key);
  }
  
#  function offsetUnset($key)
#  {
#    $this->unloaded and $this->load();
#    return parent::offsetUnset($key);
#  }
  
  function getIterator()
  {
    $this->unloaded and $this->load();
    return parent::getIterator();
  }
  
  function serialize()
  {
    $this->unloaded and $this->load();
    return parent::serialize();
  }
  
  function asort()
  {
    $this->unloaded and $this->load();
    parent::asort();
  }
  
  function ksort()
  {
    $this->unloaded and $this->load();
    parent::ksort();
  }
  
  function uasort($callback)
  {
    $this->unloaded and $this->load();
    parent::uasort($callback);
  }
  
  function uksort($callback)
  {
    $this->unloaded and $this->load();
    parent::uksort($callback);
  }
  
  function natcasesort()
  {
    $this->unloaded and $this->load();
    parent::natcasesort();
  }
  
  function natsort()
  {
    $this->unloaded and $this->load();
    parent::natsort();
  }
}

?>
