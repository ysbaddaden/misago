<?php

# Handles ORM relationships.
# 
# =Relationships
# 
# ==belongs_to
#
# Represents a one-to-one relationship, in the point of view
# of the child. For instance a comment belongs to a blog post.
# The counterpart is either a has_one or has_many relationship
# (see below).
# 
# ===Example: 
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
# ==has_one
# 
# Represents a one-to-one relationship, in the point of view
# of the parent. For instance an order has one invoice. The
# counterpart is a belongs_to relationship.
# 
# The difference with a belongs_to relationship, is where the
# foreign key is located: in this model for a belongs_to
# relationship; in the related model for a has_one relationship.
# 
# ===Example: 
# 
#   class Order extends ActiveRecord_Base {
#     public $has_one = 'invoice';
#   }
#   
#   class Invoice extends ActiveRecord_Base {
#     public $belongs_to = 'order';
#   }
# 
# 
# ==has_many
# 
# Represents a one-to-many relationship, in the point of view
# of the parent. For instance a post may have many comments.
# The counterpart is a belongs_to relationship.
# 
# ===Example: 
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
# ===:through
# 
# Declares a many-to-many relationship through a join model. The
# relationship will be defined in both models.
# 
# [TODO]
# 
# 
# ==has_and_belongs_to_many
# 
# Declares a many-to-many relationship through a join table with no
# model nor primary key. The relationship must be defined in both
# models.
# 
# ===Example: 
# 
#   class Programmer extends ActiveRecord_Base {
#     protected $has_and_belongs_to_many = array('projects');
#   }
# 
#   class Project extends ActiveRecord_Base {
#     protected $has_and_belongs_to_many = array('programmers');
#   }
# 
# 
# =Eager Loading (:include)
#
# Permits to limitate repetitive requests.
# 
# Let's say you want the list of tags for each posts on a blog index page.
# That would require as many requests as there are posts to be displayed.
# Thus for 100 posts there would be 101 requests: 1 for the list of posts,
# plus 100 for loading tags for each post.
# 
# Of course it's even badder if you want, say, the list of authors, the list of
# comments, and more : you would add 100 requests each time! That's quite some
# SQL overhead.
# 
# With eager loading, such requests will be reduced to one for each relationship.
# Our previous example that required 101 requests, would now be reduced to just
# 2 requests. That's better.
# 
# ==Example: 
# 
#   # only 3 sql requests will be issued (instead of 301):
#   $post = new Post();
#   $posts = $post->find(':all', array(
#     'limit'   => 100,
#     'order'   => 'created_at desc',
#     'include' => 'tags, authors',
#   ));
#   foreach($posts as $post)
#   {
#     print_r($post->tags);
#     print_r($post->authors);
#   }
# 
# TODO: Implement create_other & create_others magic methods.
# TODO: Implement has_and_belongs_to_many association.
# TODO: Implement belongs_to :polymorphic association.
# TODO: Implement has_many :through association.
# 
# @package ActiveRecord
abstract class ActiveRecord_Associations extends ActiveRecord_Record
{
  protected $associations = array();
  protected $belongs_to   = array();
  protected $has_one      = array();
  protected $has_many     = array();
  protected $has_and_belongs_to_many = array();
  
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
        $def = array();
      }
      else
      {
        $name = $i;
        $def  = $this->{$type}[$name];
      }
      $def['type'] = $type;
      
      if (empty($def['class'])) {
        $def['class_name'] = String::camelize(String::singularize(String::underscore($name)));
      }
      if (empty($def['table'])) {
        $def['table'] = String::pluralize(String::underscore($def['class_name']));
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
          $def['find_key']   = $this->primary_key;
          $def['find_value'] = $def['foreign_key'];
          $def['find_scope'] = ':first';
        break;
        
        case 'has_one':
          $def['find_key']   = $def['foreign_key'];
          $def['find_value'] = $this->primary_key;
          $def['find_scope'] = ':first';
        break;
        
        case 'has_many':
          $def['find_key']   = $def['foreign_key'];
          $def['find_value'] = $this->primary_key;
          $def['find_scope'] = ':all';
        break;
        
        case 'has_and_belongs_to_many':
        
        break;
      }
      $this->associations[$name] = $def;
    }
  }
  
  function __get($attribute)
  {
  	# association?
		if (array_key_exists($attribute, $this->associations))
		{
		  $type   = $this->associations[$attribute]['type'];
      $model  = $this->associations[$attribute]['class_name'];
			$record = new $model();
			$conditions = array($this->associations[$attribute]['find_key'] => $this->{$this->associations[$attribute]['find_value']});
			
			$found = $record->find(
			  $this->associations[$attribute]['find_scope'],
		    array('conditions' => &$conditions)
	    );
	    
	    return $this->$attribute = ($found instanceof ArrayAccess) ?
	      new ActiveRecord_Collection($this, $found, $this->associations[$attribute]) : $found;
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
      $fk    = $this->associations[$include]['find_key'];
      $model = $this->associations[$include]['class_name'];
      $assoc = new $model();
      
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
          $assoc_key  = $record->associations[$include]['foreign_key'];
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
            $record->$include = new ActiveRecord_Collection($record, $_results, $this->associations[$include]);
          }
        break;
        
        case 'has_and_belongs_to_many':
          
        break;
      }
    }
  }
}

?>
