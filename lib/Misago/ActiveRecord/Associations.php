<?php
namespace Misago\ActiveRecord;
use Misago\ActiveSupport\String;

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
#   class Post Extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::has_many('comments');
#     }
#   }
#   
#   class Comment Extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::belongs_to('post');
#     }
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
#   class Order extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::has_one('invoice');
#     }
#   }
#   
#   class Invoice extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::belongs_to('order');
#     }
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
#   class Post Extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::has_many('tags');
#     }
#   }
#   
#   class Tag Extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::belongs_to('post');
#     }
#   }
#   
#   $post = new Post(123);
#   foreach($post->tags as $tag) {
#     echo $tag->name.", ";
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
#   class Programmer extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::has_and_belongs_to_many('projects');
#     }
#   }
# 
#   class Project extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::has_and_belongs_to_many('programmers');
#     }
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
# See <tt>Misago\ActiveRecord\Collection</tt> for details.
# 
# = Dependent relations
# 
# You may want to delete, destroy or nullify dependent relations when
# you delete a record. For instance when deleting a blog post, you better
# delete all associated comments and tags.
# 
# By default this is not activated. You have to define it.
# 
#   class Post extends Misago\ActiveRecord\Base
#   {
#     static function __constructStatic()
#     {
#       static::has_to_many('projects', array('dependent' => 'destroy_all'));
#       static::has_to_many('comments', array('dependent' => 'delete_all'));
#     }
#   } 
# 
# Depending on the type of relation, you will have access to different
# dependent actions:
# 
# == belongs_to
# 
# - +destroy+ - calls the +destroy+ method of associated object.
# - +delete+  - calls the +delete+ method of associated object.
# 
# == has_one
# 
# - +destroy+ - calls the +destroy+ method of associated object.
# - +delete+  - calls the +delete+ method of associated object.
# - +nullify+ - nullifies the foreign key of associated object.
# 
# == has_many
# 
# - +destroy+    - calls the +destroy+ method of all associated objects.
# - +delete_all+ - calls the +delete_all+ method of the collection.
# - +nullify+    - nullifies the foreign key on associated objects.
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
#   $posts = Post::find(':all', array(
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
# TODO: Implement has_many/has_one :throught association.
abstract class Associations extends Record
{
  private static $_associations;
  
  
  # Returns an association's configuration. Returns null if no such
  # association exists.
  static function & association($assoc)
  {
    $options = isset(self::$_associations[get_called_class()][$assoc]) ?
      self::$_associations[get_called_class()][$assoc] : null;
    
    if ($options !== null
      and $options['type'] == 'has_many'
      and isset($options['through']))
    {
      $through    = static::association($options['through']);
      $through    = $through['class_name'];
      $assoc      = $through::association(String::singularize($options['name']));
      $class_name = $assoc['class_name'];
      
      $options['class_name']  = $assoc['class_name'];
      $options['primary_key'] = $assoc['primary_key'];
      $options['through_foreign_key'] = $assoc['foreign_key'];
      
      $options['find_key']  = $through::table_name().'.'.$options['foreign_key'];
      if (!isset($options['find_options']['select'])) {
        $options['find_options']['select'] = $assoc['table_name'].'.*';
      }
      $options['find_options']['joins'] = $options['through'];
    }
    return $options;
  }
  
  static function has_association($assoc)
  {
    return isset(self::$_associations[get_called_class()][$assoc]);
  }
  
  # Returns the list of associations.
  static function & association_names()
  {
    $associations = isset(self::$_associations[get_called_class()]) ?
      array_keys(self::$_associations[get_called_class()]) : array();
    return $associations;
  }
  
  private static function set_association($type, $name, $options)
  {
    $options['type'] = $type;
    $options['name'] = $name;
    self::$_associations[get_called_class()][$name] = $options;
  }
  
  private static function prepare_association_options($name, &$options)
  {
    if (empty($options['class_name'])) {
      $options['class_name'] = String::camelize(String::singularize(String::underscore($name)));
    }
    $class_name = $options['class_name'];
    
    if (empty($options['table_name'])) {
      $options['table_name'] = $class_name::table_name();
    }
    if (empty($options['primary_key'])) {
      $options['primary_key'] = $class_name::primary_key();
    }
  }
  
  
  protected static function belongs_to($name, $options=array())
  {
    static::prepare_association_options($name, $options);
    
    if (empty($options['foreign_key'])) {
      $options['foreign_key'] = String::underscore($options['class_name']).'_id';
    }
    $options['find_key']     = static::primary_key();
    $options['find_scope']   = ':first';
    $options['find_options'] = array_intersect_key($options, array(
      'select'     => '',
      'conditions' => '',
      'include'    => '',
    ));
    static::set_association('belongs_to', $name, $options);
  }
  
  # TODO: add 'autosave' and 'validate' options for has_one relationships.
  protected static function has_one($name, $options=array())
  {
    static::prepare_association_options($name, $options);
    
    if (empty($options['foreign_key'])) {
      $options['foreign_key'] = String::underscore(get_called_class()).'_id';
    }
    $options['find_key']     = $options['foreign_key'];
    $options['find_scope']   = ':first';
    $options['find_options'] = array_intersect_key($options, array(
      'select'     => '',
      'conditions' => '',
      'include'    => '',
      'order'      => '',
    ));
    static::set_association('has_one', $name, $options);
  }
  
  # TODO: add 'autosave' and 'validate' options for has_many relationships.
  protected static function has_many($name, $options=array())
  {
    static::prepare_association_options($name, $options);
    
    if (empty($options['foreign_key'])) {
      $options['foreign_key'] = String::underscore(get_called_class()).'_id';
    }
    $options['find_scope']   = ':all';
    $options['find_options'] = array_intersect_key($options, array(
      'select'     => '',
      'conditions' => '',
      'order'      => '',
      'limit'      => '',
      'page'       => '',
      'group'      => '',
      'include'    => '',
    ));
    
    # has_many through
    if (!isset($options['through']))
    {
      /*
      $through    = static::association($options['through']);
      $through    = $through['class_name'];
      $assoc      = $through::association(String::singularize($name));
      $class_name = $assoc['class_name'];
      
      $options['class_name']  = $assoc['class_name'];
      $options['primary_key'] = $assoc['primary_key'];
      $options['through_foreign_key'] = $assoc['foreign_key'];
      
      $options['find_key']  = $through::table_name().'.'.$options['foreign_key'];
      if (!isset($options['find_options']['select'])) {
        $options['find_options']['select'] = $assoc['table_name'].'.*';
      }
      $options['find_options']['joins'] = $options['through'];
    }
    else {
      */
      $options['find_key'] = $options['foreign_key'];
    }
    
    static::set_association('has_many', $name, $options);
  }
  
  # TODO: add 'autosave' and 'validate' options for HABTM relationships.
  protected static function has_and_belongs_to_many($name, $options=array())
  {
    static::prepare_association_options($name, $options);
    
    if (empty($options['foreign_key'])) {
      $options['foreign_key'] = String::underscore(get_called_class()).'_id';
    }
    if (empty($options['join_table']))
    {
      $table_name = static::table_name();
      $options['join_table'] = ($table_name < $options['table_name']) ?
        $table_name.'_'.$options['table_name'] :
        $options['table_name'].'_'.$table_name;
    }
    if (empty($options['association_primary_key'])) {
      $options['association_primary_key'] = 'id';
    }
    if (empty($options['association_foreign_key'])) {
      $options['association_foreign_key'] = String::underscore($options['class_name']).'_id';
    }
    
    $connection = static::connection();
    
    $options['find_key']   = "{$options['join_table']}.{$options['foreign_key']}";
    $options['find_scope'] = ':all';
    $options['find_options'] = array_intersect_key($options, array(
      'select'     => '',
      'conditions' => '',
      'group'      => '',
#     'having'     => '',
      'order'      => '',
      'limit'      => '',
      'page'       => '',
      'include'    => '',
    ));
    $options['find_options']['joins'] = "inner join ".$connection->quote_table($options['join_table']).
      " on ".$connection->quote_column("{$options['join_table']}.{$options['association_foreign_key']}").
      " = ".$connection->quote_column("{$options['table_name']}.{$options['association_primary_key']}");
    
    static::set_association('has_and_belongs_to_many', $name, $options);
  }
  
  # Shortcut for +has_and_belongs_to_many+.
  protected static function habtm($name, $options=array()) {
    return static::has_and_belongs_to_many($name, $options);
  }
  
  function __get($attribute)
  {
  	# association?
		if (static::has_association($attribute))
		{
		  $assoc      = static::association($attribute);
      $class_name = $assoc['class_name'];
      
      switch($assoc['type'])
      {
        case 'has_one': case 'belongs_to':
		      if (!$this->new_record)
		      {
			      $conditions = ($assoc['type'] == 'belongs_to') ?
			        array($assoc['find_key'] => $this->{$assoc['foreign_key']}) :
      		    array($assoc['find_key'] => $this->id);
			      $options['conditions'] = empty($options['conditions']) ? $conditions :
		          static::merge_conditions($options['conditions'], $conditions);
		        
			      $found = $class_name::find($assoc['find_scope'], $options);
			      if ($found) {
			        return $this->$attribute = $found;
			      }
          }
          return $this->$attribute = new $class_name();
        break;
        
        case 'has_many': case 'has_and_belongs_to_many':
          return $this->$attribute = new Collection($this, null, $assoc);
        break;
      }
		}
		
    # list of ids for a has_many or HABTM relation?
		elseif(preg_match('/(.+)_ids/', $attribute, $match))
		{
		  $assoc_name = String::pluralize($match[1]);
		  
		  if (static::has_association($assoc_name))
		  {
		    $assoc = static::association($assoc_name);
		    if ($assoc['type'] == 'has_many'
		      or $assoc['type'] == 'has_and_belongs_to_many')
		    {
          $class_name = $assoc['class_name'];
		      
		      $conditions = array($assoc['find_key'] => $this->id);
		      $options['conditions'] = empty($options['conditions']) ? $conditions :
		        static::merge_conditions($options['conditions'], $conditions);
		      $options['select'] = $class_name::primary_key();
	        
          $sql = $class_name::build_sql_from_options($options);
		      return $class_name::connection()->select_values($sql);
		    }
	    }
		}
  	
    # another kind of attribute
    return parent::__get($attribute);
  }
  
  function __call($fn, $args)
  {
    if (preg_match('/^(create|build)_(.+)$/', $fn, $match))
    {
      $assoc_name = $match[2];
      
      if (static::has_association($assoc_name))
      {
        $assoc = static::association($assoc_name);
        if ($assoc['type'] == 'belongs_to'
          or $assoc['type'] == 'has_one')
        {
          $class_name  = $assoc['class_name'];
          $attributes  = isset($args[0]) ? $args[0] : $args;
          
          switch ($assoc['type'])
          {
            case 'belongs_to': $attributes[static::primary_key()] = $this->{$assoc['foreign_key']}; break;
            case 'has_one':    $attributes[$assoc['foreign_key']] = $this->id; break;
          }
          $this->$assoc_name = new $class_name($attributes);
          
          if ($match[1] == 'create') {
            $this->$assoc_name->save();
          }
          return $this->$assoc_name;
        }
      }
    }
    return parent::__call($fn, $args);
  }
  
  function __sleep()
  {
  	$attributes   = parent::__sleep();
  	$associations = static::association_names();
  	foreach($associations as $k)
  	{
  		if (property_exists($this, $k)) {
  			$attributes[] = $k;
  		}
  	}
  	return $attributes;
  }
  
  protected static function eager_loading($records, $includes)
  {
    if (count($records) == 0) {
      return;
    }
    
    foreach(array_collection($includes) as $include)
    {
      $assoc    = static::association($include);
      $find_key = $assoc['find_key'];
      
      # ids
      $ids = array();
      if ($assoc['type'] == 'belongs_to')
      {
        foreach($records as $record) {
          $ids[] = $record->{$assoc['foreign_key']};
        }
      }
      else
      {
        foreach($records as $record) {
          $ids[] = $record->id;
        }
      }
      $ids = array_unique($ids);
      
      # options
			$options    = isset($assoc['find_options']) ? $assoc['find_options'] : array();
      $conditions = array($find_key => $ids);
		  $options['conditions'] = empty($options['conditions']) ? $conditions :
		    static::merge_conditions($options['conditions'], $conditions);
	    
	    # find
      $class_name = $assoc['class_name'];
      $results    = $class_name::find(':all', $options);
      
      # dispatch
      switch($assoc['type'])
      {
        case 'belongs_to':
        case 'has_one':
          if ($assoc['type'] == 'belongs_to')
          {
            $record_key = $assoc['foreign_key'];
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
          $assoc_key = $assoc['foreign_key'];
          
          foreach($records as $record)
          {
            $_results = array();
            foreach($results as $rs)
            {
              if ($rs->{$assoc_key} == $record->id) {
                $_results[] = $rs;
              }
            }
            $record->$include = new Collection($record, $_results, $assoc);
          }
        break;
      }
    }
  }

  # Creates a SQL join fragment for a given association (related to this).
  # 
  # - +$association+ - the association to build the SQL join fragment with.
  # - +$type+        - inner, outer, left, left outer, etc.
  # 
  static function build_join_for($association, $type="inner")
  {
    $connection = static::connection();
    $assoc      = static::association($association);
    
    switch($assoc['type'])
    {
      case 'belongs_to':
        return "$type join ".$connection->quote_table($assoc['table_name']).
          " on ".$connection->quote_column("{$assoc['table_name']}.{$assoc['primary_key']}").
          " = ".$connection->quote_column(static::table_name().".{$assoc['foreign_key']}");
      
      case 'has_one':
      case 'has_many':
        return "$type join ".$connection->quote_table($assoc['table_name']).
          " on ".$connection->quote_column("{$assoc['table_name']}.{$assoc['foreign_key']}").
          " = ".$connection->quote_column(static::table_name().".".static::primary_key());
      
      case 'has_and_belongs_to_many':
        return "$type join ".$connection->quote_table($assoc['join_table']).
          " on ".$connection->quote_column("{$assoc['join_table']}.{$assoc['foreign_key']}").
          " = ".$connection->quote_column(static::table_name().".".static::primary_key()).
          " $type join ".$connection->quote_table($assoc['table_name']).
          " on ".$connection->quote_column("{$assoc['table_name']}.{$assoc['association_primary_key']}").
          " = ".$connection->quote_column("{$assoc['join_table']}.{$assoc['association_foreign_key']}");
    }
  }
  
  # TODO: save_associated() must save HABTM relationships.
  # :private:
  protected function save_associated()
  {
    foreach(static::association_names() as $assoc_name)
    {
      $assoc = static::association($assoc_name);
      
      if (isset($this->{$assoc['name']}))
      {
        switch($assoc['type'])
        {
          case 'has_one':
            $primary_key = static::primary_key();
            $this->$assoc_name->{$assoc['foreign_key']} = $this->$primary_key;
          break;
          case 'has_many': break;
          case 'has_and_belongs_to_many': break;
          case 'belongs_to': continue;
        }
        
        if (!$this->$assoc_name->save()) {
          return false;
        }
      }
    }
    return true;
  }
  
  # :private:
  protected function delete_associated()
  {
    $rs = true;
    foreach(static::association_names() as $assoc_name)
    {
      $assoc = static::association($assoc_name);
      
      if (empty($assoc['dependent'])) {
        continue;
      }
      
      switch($assoc['type'])
      {
        case 'has_one':
          switch($assoc['dependent'])
          {
            case 'delete': $this->$assoc_name->delete(); break 2;
            case 'destroy':
              $obj = new $assoc['class_name']();
              $obj->destroy_all(array($assoc['foreign_key'] => $this->id));
            break 2;
            case 'nullify':
              $obj = new $assoc['class_name']();
              $obj->update_all(array($assoc['foreign_key'] => null),
                array($assoc['foreign_key'] => $this->id));
            break 2;
          }
        break;
        
        case 'belongs_to':
          switch($assoc['dependent'])
          {
            case 'delete': $this->$assoc_name->delete(); break 2;
            case 'destroy':
              $obj = new $assoc['class_name']();
              $obj->destroy_all(array($assoc['primary_key'] => $this->id));
            break 2;
          }
        break;
        
        case 'has_many':
          if (!isset($this->options['through']))
          {
            switch($assoc['dependent'])
            {
              case 'delete_all': $this->$assoc_name->delete_all(); break 2;
              case 'destroy':
                $obj = new $assoc['class_name']();
                $obj->destroy_all(array($assoc['foreign_key'] => $this->id));
              break 2;
              case 'nullify':
                $obj = new $assoc['class_name']();
                $obj->update_all(array($assoc['foreign_key'] => null),
                  array($assoc['foreign_key'] => $this->id));
              break 2;
            }
          }
        break;
        
        case 'has_and_belongs_to_many':
          switch($assoc['dependent'])
          {
            case 'delete_all': $this->$assoc_name->delete_all(); break 2;
            case 'destroy':
              trigger_error("dependent => destroy not (yet) supported by HABTM relationships", E_USER_ERROR);
            break 2;
            case 'nullify':
              trigger_error("dependent => nullify not (yet) supported by HABTM relationships", E_USER_ERROR);
            break 2;
          }
        break;
      }
    }
    return $rs;
  }
}

?>
