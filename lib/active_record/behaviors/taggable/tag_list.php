<?php

# IMPROVE: Cache the list into parent's column 'cached_tags' (if column exists).
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
  function find($tags, $options=null)
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
  function find_options($tags, $options)
  {
    $match_all = isset($options['match_all']) ? $options['match_all'] : false;
    $conditions = $this->find_conditions($tags, $match_all);
    
    $options['select']     = "{$this->parent->table_name}.*";
    $options['join']       = $this->tag_association;
    $options['conditions'] = $this->parent->merge_conditions($options, $conditions);
  }
  
  # IMPROVE: implement option 'match_all = true'.
  # @private
  function find_conditions($tags, $match_all=false)
  {
    $tags = array_collection($tags);
    $conditions = array("{$this->assoc['table_name']}.{$this->tag_column}" => &$tags);
    return $conditions;
  }
  
  # @private
  function count_options($tags, $options)
  {
    $options = $this->find_options($tags, $options);
    $options['select'] = "{$this->options['table_name']}.{$this->tag_column}, COUNT(*)";
  }
}

?>
