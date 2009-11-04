<?php

# Model object relationships.
# 
# =Relationships
# 
# ==belongs_to
#
# Represents a one-to-one relationship, in the point of view
# of the child. The counterpart is either a has_one or
# has_many relationship (see below).
# 
# ===Example: 
# 
# A comment belongs to a blog post.
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
# ===Options for belongs to
# 
# - class_name
# - conditions
# - dependent
# - foreign_key
# - include
# - select
# - table_name
# 
# 
# ==has_one
# 
# Represents a one-to-one relationship, in the point of view
# of the parent. The counterpart is a belongs_to relationship.
# 
# The difference with a belongs_to relationship, is where the
# foreign key is located: in this model for a belongs_to
# relationship; in the related model for a has_one relationship.
# 
# ===Example:
#
# An order has one invoice.
# 
#   class Order extends ActiveRecord_Base {
#     public $has_one = 'invoice';
#   }
#   
#   class Invoice extends ActiveRecord_Base {
#     public $belongs_to = 'order';
#   }
#   
#   $order = new Order(456);
#   $invoice_id = $order->invoice->id;
# 
# ===Options for has_one
# 
# - class_name
# - conditions
# - dependent
# - foreign_key
# - include
# - order
# - primary_key
# - select
# - table_name
# 
# 
# ==has_many
# 
# Represents a one-to-many relationship, in the point of view
# of the parent. The counterpart is a belongs_to relationship.
# 
# ===Example: 
# 
# A post may have many tags.
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
#	    echo $tag->name.", ";
#   }
# 
# ===Options for has_many
# 
# - class_name
# - conditions
# - dependent
# - foreign_key
# - include
# - limit
# - order
# - page
# - primary_key
# - select
# - table_name
# 
# ===:through
# 
# Declares a has_many relationship, but through a join model.
# 
# [TODO]
# 
# 
# ==has_and_belongs_to_many
# 
# Declares a many-to-many relationship, using a join table with no
# associated model nor primary key.
# 
# ===Example:
# 
# A programmer may have many projects, and a project may belong to
# (or have) many programmers.
# 
#   class Programmer extends ActiveRecord_Base {
#     protected $has_and_belongs_to_many = array('projects');
#   }
# 
#   class Project extends ActiveRecord_Base {
#     protected $has_and_belongs_to_many = array('programmers');
#   }
# 
# ===Options for has_and_belongs_to_many
# 
# - association_foreign_key
# - association_primary_key
# - class_name
# - conditions
# - dependent
# - foreign_key
# - group
# - include
# - join_table
# - limit
# - order
# - page
# - select
# - table_name
# 
# ===Join table
# 
# An HABTM relationship requires the existence of a join table
# with no primary key but containing foreign keys of both models.
# 
# The join table name is made by comparing sizes of both models'
# name, using the +<+ operator. The smaller one before. For our
# previous example, the projects-programmers relationship, the
# join table name would be +programmers_projects+.
# 
# In fact previous example requires the following table and
# fields:
# 
#   programmers_projects
#   --------------------
#   programmer_id
#   project_id
# 
#
# =Auto-generated methods and attributes
# 
# ==Singular associations (one-to-one)
# 
# Declaring a +belongs_to+ or +has_one+ association generates the following
# attributes and methods (where +other+ is the name of the relation, for
# instance +invoice+, +post+, etc).
# 
# - other
# - build_other($attributes=array())
# - create_other($attributes=array())
# 
# ==Plural associations (one-to-many / many-to-many)
# 
# Declaring a +has_many+ or +has_and_belongs_to_many+ association generates
# the following attributes and methods (where +others+ is the name of the
# relation, for instance +posts+, +comments+, etc).
# 
# - others
# - others.build($attributes=array())
# - others.create($attributes=array())
# - others.clear()
# - others.count()
# - others.find()
# - others.delete(record, record, ...)
# - others.delete_all()
# - others.destroy_all()
# 
# See <tt>ActionController_Collection</tt> for details.
# 
# = Dependent relations
# 
# You may want to delete, destroy or nullify dependent relations when
# you delete a record. For instance when deleting a blog post, you better
# delete all associated comments and tags.
# 
# By default this is not activated. You have to define it.
# 
#   class Post extends ActiveRecord_Base
#   {
#     protected $has_many = array(
#       'comments' => array('dependent' => 'delete_all'),
#       'tags'     => array('dependent' => 'delete_all')
#     );
#   } 
# 
# Depending on the type of relation, you will have access to different
# dependent actions:
# 
# == belongs_to
# 
# - destroy: calls the +destroy+ method of associated object.
# - delete:  calls the +delete+ method of associated object.
# 
# == has_one
# 
# - destroy: calls the +destroy+ method of associated object.
# - delete:  calls the +delete+ method of associated object.
# - nullify: nullifies the foreign key of associated object.
# 
# == has_many
# 
# - destroy:    calls the +destroy_all+ method of the collection.
# - delete_all: calls the +delete_all+ method of the collection.
# - nullify:    nullifies the foreign key on associated objects.
# 
# =Eager Loading (include)
#
# Permits to limitate repetitive requests of relationships' data by
# requesting it all at once.
# 
# Let's say you want the list of tags for each posts on a blog index page.
# That would require as many requests as there are posts to be displayed.
# Thus for 100 posts there would be 101 requests: 1 for the list of posts,
# and 100 for loading the list of tags for each post.
# 
# Of course it's even badder if you also want, say, the list of authors,
# the list of comments, and more : you would add 100 requests each time!
# That's quite some SQL overhead, and can be dreadful.
# 
# With eager loading, such requests will be reduced to one for each
# relationship. Our previous example that required 101 requests, would now
# be reduced to just 2 requests. Which is better.
# 
# ==Example: 
# 
# Only 3 SQL requests will be issued (instead of 301):
# 
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
# FIXME: handle others.build, etc. methods for HABTM relationships.
# IMPROVE: has_many options: counter_sql, finder_sql
# IMPROVE: HABTM options: counter_sql, finder_sql, insert_sql, delete_sql
# TODO: Implement has_many/has_one :through association.
abstract class ActiveRecord_Associations extends ActiveRecord_Record
{
  protected $belongs_to = array();
  protected $has_one    = array();
  protected $has_many   = array();
  protected $has_and_belongs_to_many = array();
  
  protected $associations = array();
  
  function __construct($arg=null)
  {
    $this->configure_associations();
    parent::__construct($arg);
  }
  
  private function configure_associations()
  {
    $apc_key = TMP.'/cache/active_records/associations_'.get_class($this);
    $this->associations = apc_fetch($apc_key, $success);
    if ($success === false)
    {
      $this->associations = array();
      $this->_configure_associations('belongs_to');
      $this->_configure_associations('has_one');
      $this->_configure_associations('has_many');
      $this->_configure_associations('has_and_belongs_to_many');
      apc_store($apc_key, $this->associations);
    }
  }
  
  private function _configure_associations($type)
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
      $def['name'] = $name;
      $def['type'] = $type;
      
      if (empty($def['class_name'])) {
        $def['class_name'] = String::camelize(String::singularize(String::underscore($name)));
      }
      if (empty($def['table_name'])) {
        $def['table_name'] = String::pluralize(String::singularize(String::underscore($name)));
      }
      if (empty($def['primary_key'])) {
        $def['primary_key'] = 'id';
      }
      if (empty($def['foreign_key']))
      {
        $def['foreign_key'] = ($type == 'belongs_to') ?
          String::underscore($def['class_name']).'_id' :
          String::underscore(get_class($this)).'_id';
      }
      
      if ($type != 'has_and_belongs_to_many'
        and !isset($def['dependent']))
      {
        $def['dependent'] = null;
      }
      
      switch($type)
      {
        case 'belongs_to':
          $def['find_key']     = $this->primary_key;
          $def['find_scope']   = ':first';
          $def['find_options'] = array_intersect_key($def, array(
            'select'     => '',
            'conditions' => '',
            'include'    => '',
          ));
        break;
        
        case 'has_one':
          $def['find_key']     = $def['foreign_key'];
          $def['find_scope']   = ':first';
          $def['find_options'] = array_intersect_key($def, array(
            'select'     => '',
            'conditions' => '',
            'include'    => '',
            'order'      => '',
          ));
        break;
        
        case 'has_many':
          $def['find_key']     = $def['foreign_key'];
          $def['find_scope']   = ':all';
          $def['find_options'] = array_intersect_key($def, array(
            'select'     => '',
            'conditions' => '',
            'order'      => '',
            'limit'      => '',
            'page'       => '',
            'group'      => '',
            'include'    => '',
          ));
        break;
        
        case 'has_and_belongs_to_many':
          if (empty($def['join_table']))
          {
            $def['join_table'] = ($this->table_name < $def['table_name']) ?
              $this->table_name.'_'.$def['table_name'] : $def['table_name'].'_'.$this->table_name;
          }
          if (empty($def['association_primary_key'])) {
            $def['association_primary_key'] = 'id';
          }
          if (empty($def['association_foreign_key'])) {
            $def['association_foreign_key'] = String::underscore($def['class_name']).'_id';
          }
          
          $def['find_key']   = "{$def['join_table']}.{$def['foreign_key']}";
          $def['find_scope'] = ':all';
          $def['find_options'] = array_intersect_key($def, array(
            'select'     => '',
            'conditions' => '',
            'group'      => '',
#            'having'     => '',
            'order'      => '',
            'limit'      => '',
            'page'       => '',
            'include'    => '',
          ));
          $def['find_options']['joins'] = "inner join ".$this->connection->quote_table($def['join_table']).
            " on ".$this->connection->quote_column("{$def['join_table']}.{$def['association_foreign_key']}").
            " = ".$this->connection->quote_column("{$def['table_name']}.{$def['association_primary_key']}");
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
		  $assoc =& $this->associations[$attribute];
      $model = $assoc['class_name'];
      
		  if (!$this->new_record)
		  {
		    # parent does exist
			  $options = isset($assoc['find_options']) ? $assoc['find_options'] : array();
			  
			  $conditions = ($assoc['type'] == 'belongs_to') ?
			    array($assoc['find_key'] => $this->{$assoc['foreign_key']}) :
  		    array($assoc['find_key'] => $this->id);
			  $options['conditions'] = empty($options['conditions']) ? $conditions :
		      $this->merge_conditions($options['conditions'], $conditions);
		    
			  $record = new $model();
			  $found  = $record->find($assoc['find_scope'], $options);
        
        if ($found)
        {
          # association exists
	        return $this->$attribute = ($found instanceof ArrayAccess) ?
	          new ActiveRecord_Collection($this, $found, $assoc) : $found;
        }
      }
      
      # association doesn't exists
      if ($assoc['type'] == 'belongs_to' or $assoc['type'] == 'has_one') {
        return $this->$attribute = new $model();
      }
      return $this->$attribute = new ActiveRecord_Collection($this, array(), $assoc);
		}
		
    # list of ids for a has_many or HABTM relation?
		elseif(preg_match('/(.+)_ids/', $attribute, $match))
		{
		  $assoc_name = String::pluralize($match[1]);
		  
		  if (isset($this->associations[$assoc_name]) and
		    ($this->associations[$assoc_name]['type'] == 'has_many'
		      or $this->associations[$assoc_name]['type'] == 'has_and_belongs_to_many'))
		  {
		    $assoc  =& $this->associations[$assoc_name];
        $model  = $assoc['class_name'];
		    $record = new $model();
		    
		    $conditions = array($assoc['find_key'] => $this->id);
		    $options['conditions'] = empty($options['conditions']) ? $conditions :
		      $this->merge_conditions($options['conditions'], $conditions);
		    $options['select'] = $record->primary_key;
	      
		    $sql = $record->build_sql_from_options($options);
		    return $record->connection->select_values($sql);
		  }
		}
  	
    # another kind of attribute
    return parent::__get($attribute);
  }
  
  function __call($fn, $args)
  {
    if (preg_match('/^(create|build)_(.+)$/', $fn, $match))
    {
      $assoc = $match[2];
      
      if (isset($this->associations[$assoc])
        and ($this->associations[$assoc]['type'] == 'belongs_to'
        or $this->associations[$assoc]['type'] == 'has_one'))
      {
        $class = $this->associations[$assoc]['class_name'];
        $fk    = $this->associations[$assoc]['foreign_key'];
        $attributes = isset($args[0]) ? $args[0] : $args;
        
        switch ($this->associations[$assoc]['type'])
        {
          case 'belongs_to': $attributes[$this->primary_key] = $this->$fk; break;
          case 'has_one':    $attributes[$fk] = $this->id; break;
        }
        $this->$assoc = new $class($attributes);
        
        if ($match[1] == 'create') {
          $this->$assoc->save();
        }
        return $this->$assoc;
      }
    }
    return parent::__call($fn, $args);
  }
  
  function __sleep()
  {
  	$attributes = parent::__sleep();
  	foreach(array_keys($this->associations) as $k)
  	{
  		if (property_exists($this, $k)) {
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
    
    foreach(array_collection($includes) as $include)
    {
      $find_key = $this->associations[$include]['find_key'];
      $model    = $this->associations[$include]['class_name'];
      
      # ids
      $ids = array();
      if ($this->associations[$include]['type'] == 'belongs_to')
      {
        foreach($records as $record) {
          $ids[] = $record->{$this->associations[$include]['foreign_key']};
        }
      }
      else
      {
        foreach($records as $record) {
          $ids[] = $record->id;
        }
      }
      
      # options
			$options = isset($this->associations[$include]['find_options']) ?
			  $this->associations[$include]['find_options'] : array();
		  
      $conditions = array($find_key => array_unique($ids));
		  $options['conditions'] = empty($options['conditions']) ? $conditions :
		    $this->merge_conditions($options['conditions'], $conditions);
	    
	    # find
      $assoc   = new $model();
      $results = $assoc->find(':all', $options);
      
      # dispatch
      switch($this->associations[$include]['type'])
      {
        case 'belongs_to':
        case 'has_one':
          if ($this->associations[$include]['type'] == 'belongs_to')
          {
            $record_key = $this->associations[$include]['foreign_key'];
            $rs_key = 'id';
          }
          else
          {
            $record_key = 'id';
            $rs_key = $find_key;
          }
          
          foreach($records as $record)
          {
            $r = null;
            foreach($results as $rs)
            {
              if ($record->$record_key == $rs->$rs_key)
              {
                $r = $rs;
                break;
              }
            }
            $record->$include = $r;
          }
        break;
        
        case 'has_many':
        case 'has_and_belongs_to_many':
          $assoc_key = $record->associations[$include]['foreign_key'];
          
          foreach($records as $record)
          {
            $_results = array();
            foreach($results as $rs)
            {
              if ($rs->{$assoc_key} == $record->id) {
                $_results[] = $rs;
              }
            }
            $record->$include = new ActiveRecord_Collection(
              $record, $_results, $this->associations[$include]);
          }
        break;
      }
    }
  }

  # Creates a SQL join fragment for a given association (related to this).
  # 
  # - $association: the association to build the SQL join fragment with.
  # - $type: inner, outer, left, left outer, etc.
  # 
  function build_join_for($association, $type="inner")
  {
    $assoc = $this->associations[$association];
    switch($assoc['type'])
    {
      case 'belongs_to':
        return "$type join ".$this->connection->quote_table($assoc['table_name']).
          " on ".$this->connection->quote_column("{$assoc['table_name']}.{$assoc['primary_key']}").
          " = ".$this->connection->quote_column("{$this->table_name}.{$assoc['foreign_key']}");
      
      case 'has_one':
      case 'has_many':
        return "$type join ".$this->connection->quote_table($assoc['table_name']).
          " on ".$this->connection->quote_column("{$assoc['table_name']}.{$assoc['foreign_key']}").
          " = ".$this->connection->quote_column("{$this->table_name}.{$this->primary_key}");
      
      case 'has_and_belongs_to_many':
        return "$type join ".$this->connection->quote_table($assoc['join_table']).
          " on ".$this->connection->quote_column("{$assoc['join_table']}.{$assoc['foreign_key']}").
          " = ".$this->connection->quote_column("{$this->table_name}.{$this->primary_key}").
          " $type join ".$this->connection->quote_table($assoc['table_name']).
          " on ".$this->connection->quote_column("{$assoc['table_name']}.{$assoc['association_primary_key']}").
          " = ".$this->connection->quote_column("{$assoc['join_table']}.{$assoc['association_foreign_key']}");
    }
  }
  
  
  # TEST: Test save_associated() with belongs_to, has_one, has_many & HABTM relationships.
  # :private:
  protected function save_associated()
  {
    $rs = true;
    foreach(array_keys($this->associations) as $assoc)
    {
      if (isset($this->$assoc))
      {
        $fk = $this->associations[$assoc]['foreign_key'];
        switch($this->associations[$assoc]['type'])
        {
          case 'belongs_to': $this->$assoc->{$this->primary_key} = $this->$fk; break;
          case 'has_one':    $this->$assoc->$fk = $this->$fk; break;
          case 'has_many':   break;
#          case 'has_and_belongs_to_many': break;
        }
        $rs &= $this->$assoc->save();
      }
    }
    return $rs;
  }
  
  # TEST: Test delete_associated() with belongs_to, has_one & has_many relationships.
  # :private:
  protected function delete_associated()
  {
    $rs = true;
    foreach(array_keys($this->associations) as $assoc)
    {
      switch($this->associations[$assoc]['type'])
      {
        case 'belongs_to':
          switch($this->associations[$assoc]['dependent'])
          {
            case 'delete': $this->$assoc->delete(); break;
            case 'destroy':
              $obj = new $this->associations[$assoc]['class_name']();
              $obj->destroy_all(array($this->associations[$assoc]['primary_key'] => $this->id));
            break;
          }
        break;
        
        case 'has_many':
          switch($this->associations[$assoc]['dependent'])
          {
            case 'delete_all': $this->$assoc->delete_all(); break;
            case 'destroy':
              $obj = new $this->associations[$assoc]['class_name']();
              $obj->destroy_all(array($this->associations[$assoc]['foreign_key'] => $this->id));
            break;
            case 'nullify':
              $obj = new $this->associations[$assoc]['class_name']();
              $obj->update_all(array($this->associations[$assoc]['foreign_key'] => null), array($this->associations[$assoc]['foreign_key'] => $this->id));
            break;
          }
        break;
        
        case 'has_one':
          switch($this->associations[$assoc]['dependent'])
          {
            case 'delete': $this->$assoc->delete(); break;
            case 'destroy':
              $obj = new $this->associations[$assoc]['class_name']();
              $obj->destroy_all(array($this->associations[$assoc]['foreign_key'] => $this->id));
            break;
            case 'nullify':
              $obj = new $this->associations[$assoc]['class_name']();
              $obj->update_all(array($this->associations[$assoc]['foreign_key'] => null), array($this->associations[$assoc]['foreign_key'] => $this->id));
            break;
          }
        break;
      }
    }
    return $rs;
  }
}

?>
