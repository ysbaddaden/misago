<?php

# IMPROVE: Cache the list into parent's column 'tags_cached' (if column exists).
class ActiveRecord_Behaviors_Taggable_TagList extends ArrayObject
{
  private $parent;
  private $collection;
  private $assoc;
  private $tag_column;
  
  function __construct($parent, $assoc)
  {
    $this->parent     = $parent;
    $this->assoc      = $assoc;
    $this->collection = $parent->{$this->assoc['name']};
    $this->tag_column = String::singularize($this->assoc['name']);
    
    parent::__construct(array());
    $this->resetArray();
  }
  
  # Returns a list of records that match given tags.
  function find($tags, $options=array())
  {
    $options = $this->find_options($tags, $options);
    return $this->collection->find(':all', $options);
  }
  
  function save()
  {
    
  }
  
  function add($tag)
  {
    
  }
  
  function remove($tag)
  {
    
  }
  
  function __toString()
  {
    return implode(', ', (array)$this);
  }
    
  # @private
  function set($tags='')
  {
    if (empty($tags)) {
      $this->collection->delete_all();
    }
    else
    {
      $tags = array_unique(array_collection($tags));
      
      $old_tags = (array)$this;
      $new_tags = array_diff($tags, $old_tags);
      $del_tags = array_diff($old_tags, $tags);
      
      # creates new tags
      foreach($new_tags as $tag_name) {
        $r = $this->collection->create(array($this->tag_column => $tag_name));
      }
      
      # deletes removed tags
      if (!empty($del_tags))
      {
        # since the collection is going to be modified, we need to cast it
        # to a static array, otherwise the foreach sequence would break.
        foreach((array)$this->collection as $record)
        {
          if (in_array($record->{$this->tag_column}, $del_tags)) {
            $this->collection->delete($record);
          }
        }
      }
    }
    $this->resetArray();
  }
  
  private function resetArray()
  {
    $tags = array();
    foreach($this->collection as $record) {
      $tags[] = $record->{$this->tag_column};
    }
    sort($tags);
    $this->exchangeArray($tags);
  }
  
  # @private
  function & find_options($tags, $options=array())
  {
    $match_all = isset($options['match_all']) ? $options['match_all'] : false;
    $_options['conditions'] = $this->find_conditions($tags, $match_all);
    $_options['select'] = "{$this->parent->table_name}.*";
    $_options['joins']  = $this->assoc['name'];
    $_options['group']  = "{$this->parent->table_name}.{$this->parent->primary_key}";
    return $this->parent->merge_options($options, $_options);
  }
  
  # @private
  function & find_conditions($tags, $match_all=false)
  {
    $tags = array_collection($tags);
    $conditions = array();
    
    if (count($tags) == 1) {
      $conditions["{$this->assoc['table_name']}.{$this->tag_column}"] = $tags[0];
    }
    elseif ($match_all) {
      $conditions = $this->recursive_find_conditions($tags);
    }
    else {
      $conditions["{$this->assoc['table_name']}.{$this->tag_column}"] =& $tags;
    }
    return $conditions;
  }
  
  private function recursive_find_conditions($tags)
  {
    $tag = array_pop($tags);
    
    $options = empty($tags) ? array() :
      array('conditions' => $this->recursive_find_conditions($tags));
    $o = $this->find_options($tag, $options);
    $o['select'] = "{$this->parent->table_name}.{$this->parent->primary_key}";
    $conditions = $this->parent->build_sql_from_options($o);
    
    return "{$this->parent->table_name}.{$this->parent->primary_key} IN ( $conditions )";
  }
  
  # @private
  function & count_options($tags, $options)
  {
    $options = $this->find_options($tags, $options);
    $options['select'] = "{$this->options['table_name']}.{$this->tag_column}, COUNT(*)";
  }
}

?>
