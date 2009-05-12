<?php

# Handles ORM relationships.
# 
# ==Relationships
# 
# ===belongs_to
#
# Example: a comment belongs to a blog post.
# 
#   class Post Extends ActiveRecord_Base {
#     protected $has_many = array('comments');
#   }
#   
#   class Comment Extends ActiveRecord_Base {
#     protected $belongs_to = array('post');
#   }
#   
#   $comment    = new Comment(456);
#   $post_id    = $comment->post->id;
#   $post_title = $comment->post->title;
# 
# 
# ===has_many
# 
# Example: a blog may have many tags.
# 
#   class Post Extends ActiveRecord_Base {
#     protected $has_many = array('tags');
#   }
#   
#   class Tag Extends ActiveRecord_Base {
#     protected $belongs_to = array('post');
#   }
#   
#   $post = new Post(123);
#   foreach($post->tags as $tag) {
#	    echo $tag->name;
#   }
# 
# 
# ===has_one
# 
# ...
# 
# 
# ===has_and_belongs_to_many
# 
# ...
# 
# 
# ==:throught
# 
# ...
# 
# == Eager Loading (:include)
#
# Permits to limitate repetitive requests.
# 
# For instance you want the list of tags for each posts on a blog index page.
# It requires executing as many requests as there are posts displayed. So,
# for a 100 posts, there would be 101 requests: 1 for the list of posts, plus
# 100 for each post tags.
# 
# Of course it's even badded if you want the list of authors, the list of
# comments, etc. Since you will had 100 requests each time!
# 
# With eager loading it will be reduced to 2 requests: 1 for the list of posts,
# plus one for the list of tags (filtered by post).
# 
# Example: 
# 
#   $post = new Post();
#   $posts = $post->find(':all', array(
#     'limit'   => 10,
#     'order'   => 'created_at desc',
#     'include' => 'tags',
#   ));
# 
# TODO: Implement :throught associations.
# TODO: Implement has_and_belongs_to_many association.
# TODO: Implement eager loading.
# 
# @package ActiveRecord
abstract class ActiveRecord_Associations extends ActiveRecord_Record
{
  protected $associations = array();
  protected $belongs_to   = array();
  protected $has_one      = array();
  protected $has_many     = array();
  
  function __construct($arg=null)
  {
    $this->configure_associations('belongs_to');
    $this->configure_associations('has_one');
    $this->configure_associations('has_many');
    parent::__construct($arg);
  }
  
  private function configure_associations($type)
  {
    foreach($this->$type as $i => $assoc)
    {
      if (is_integer($i))
      {
        $name = $assoc;
        unset($this->$type[$i]);
        $this->$type[$name] = array();
      }
      else {
        $name = $i;
      }
      $def =& $this->{$type}[$name];
      
      if (empty($def['table'])) {
        $def['table'] = String::pluralize(String::underscore($name));
      }
      if (empty($def['primary_key'])) {
        $def['primary_key'] = 'id';
      }
      if (empty($def['foreign_key']))
      {
        switch($type)
        {
          case 'belongs_to': $def['foreign_key'] = String::underscore($name).'_'.$def['primary_key'];           break;
          case 'has_one':    $def['foreign_key'] = String::underscore(get_class($this)).'_'.$this->primary_key; break;
          case 'has_many':   $def['foreign_key'] = String::underscore(get_class($this)).'_'.$this->primary_key; break;
        }
      }
      
      switch($type)
      {
        case 'belongs_to':
          $this->associations[$name] = array(
          	'type'  => 'belongs_to',
          	'class' => String::camelize($name),
          	'key'   => $this->primary_key,
          	'value' => $def['foreign_key'],
          	'find'  => ':first',
          );
        break;
        
        case 'has_one':
          $this->associations[$name] = array(
          	'type'  => 'has_one',
          	'class' => String::camelize($name),
          	'key'   => $def['foreign_key'],
          	'value' => $this->primary_key,
          	'find'  => ':first',
          );
        break;
        
        case 'has_many':
          $this->associations[$name] = array(
          	'type'  => 'has_many',
          	'class' => String::camelize(String::singularize($name)),
          	'key'   => $def['foreign_key'],
          	'value' => $this->primary_key,
          	'find'  => ':all',
          );
        break;
      }
    }
  }
  
  function __get($attribute)
  {
  	# association?
		if (array_key_exists($attribute, $this->associations))
		{
      $class      = $this->associations[$attribute]['class'];
			$record     = new $class();
			return $this->$attribute = $record->find($this->associations[$attribute]['find'], array(
				'conditions' => array($this->associations[$attribute]['key'] => $this->{$this->associations[$attribute]['value']})
			));
		}
  	
    # another kind of attribute
    return parent::__get($attribute);
  }
  
  function __sleep()
  {
  	$attributes = parent::__sleep();
  	foreach(array_keys($this->associations) as $k)
  	{
  		if (isset($this->$k)) {
  			$attributes[] = $k;
  		}
  	}
  	return $attributes;
  }
  
  function __wakeup()
  {
  	$this->configure_associations();
  	parent::__wakeup();
  }
  
  protected function eager_loading($records, $includes)
  {
    if (count($records) == 0) {
      return;
    }
    
    $ids = array();
    foreach($records as $record)
    {
      $id = $record->{$record->primary_key};
      $ids[$id] = $record;
    }
    
    foreach(array_collection($includes) as $include)
    {
      $fk      = $this->associations[$include]['key'];
      $class   = $this->associations[$include]['class'];
      $assoc   = new $class();
      $results = $assoc->find(':all', array(
				'conditions' => array($fk => array_keys($ids))
      ));
      
      switch($this->associations[$include]['type'])
      {
        case 'belongs_to':
        case 'has_one':
          foreach($results as $rs)
          {
            $id = $rs->$fk;
            $ids[$id]->$include = $rs;
          }
          foreach($ids as $record)
          {
            if (!isset($record->$include)) {
              $record->$include = null;
            }
          }
        break;
        
        case 'has_many':
          $assoc_key  = $record->has_many[$include]['foreign_key'];
          $record_key = $record->primary_key;
          foreach($records as $record)
          {
            $_results = array();
            foreach($results as $rs)
            {
              if ($rs->{$assoc_key} == $record->{$record_key}) {
                $_results[] = $rs;
              }
            }
            $record->$include = new ActiveArray($_results);
          }
        break;
      }
    }
  }
}

?>
