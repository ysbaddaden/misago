<?php

# Handles tags stored in an has many relationship.
# 
#   class Post extends ActiveRecord_Base {
#     protected $behaviors = array('taggable' => array('tags'));
#   }
#   
#   $p = new Post(5);
#   $p->tag_list = 'php, javascript';
#   $p->tag_list; # => ArrayObject('javascript', 'php')
#   
#   $p->tag_list->add('code');
#   $p->tag_list->remove('php');
#   $p->tag_list; # => ArrayObject('code', 'javascript')
#   
#   $posts = $p->find_tagged_with('javascript');
#   
#   $tags = $p->tag_count();
#   tag_cloud($tags);
# 
class ActiveRecord_Behaviors_Taggable_Base
{
  private $parent;
  private $assoc_options;
  
  function __construct($parent, $assoc_options)
  {
    $this->parent = $parent;
    $this->assoc_options = $assoc_options;
    
    $plural   = $assoc_options['name'];
    $singular = String::singularize($plural);
    
    $attributes = array(
      "{$singular}_list" => "{$singular}_list"
    );
    $functions  = array(
      "find_with_{$plural}" => 'find_with_tags',
      "{$singular}_count"   => 'tag_counts',
    );
    $this->parent->map_module($this, $attributes, $functions);
  }
  
  function __get($attr)
  {
    if ($attr == 'tag_list')
    {
      if (!isset($this->__tag_list)) {
        $this->__tag_list = new ActiveRecord_Behaviors_Taggable_TagList($this->parent, $this->assoc_options);
      }
      return $this->__tag_list;
    }
    trigger_error("No such attribute ".get_class($this)."::$attr.", E_USER_WARNING);
  }
  
  function __set($attr, $value)
  {
    if ($attr == 'tag_list')
    {
      $this->tag_list->set($value);
      return $value;
    }
    return $this->$attr = $value;
  }
  
  function find_tagged_with($tags, $options=null)
  {
    $options = $this->parent->merge_options($options, $this->tag_list->find_options($tags));
    return $this->parent->find(':all', $options);
  }
  
  function tag_counts()
  {
    $options = $this->parent->merge_options($options, $this->tag_list->counts_options($tags));
    return $this->parent->find(':values', $options);
  }
}

?>
