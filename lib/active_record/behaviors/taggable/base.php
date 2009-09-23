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
      "{$singular}_list" => "tag_list"
    );
    $functions  = array(
      "find_with_{$plural}" => 'find_tagged_with',
      "count_with_{$plural}" => 'count_tagged_with',
      "{$singular}_count"   => 'tag_count',
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
  
  function find_tagged_with($tags, $options=array())
  {
    $tags = array_collection($tags);
    if (!empty($tags))
    {
      $options = $this->tag_list->find_options($tags, $options);
      return $this->parent->find(':all', $options);
    }
    return new ActiveArray(array(), get_class($this->parent));
  }
  
  function count_tagged_with($tags, $options=array())
  {
    $tags = array_collection($tags);
    if (!empty($tags))
    {
      $options = $this->tag_list->count_options($tags, $options);
      return $this->parent->count($options);
    }
    return 0;
  }
  
  function & tag_count($options=array())
  {
    $options = $this->tag_list->tag_count_options($options);
    $tags = $this->parent->count($options);
    ksort($tags);
    return $tags;
  }
}

?>
