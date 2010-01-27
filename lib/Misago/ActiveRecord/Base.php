<?php
namespace Misago\ActiveRecord;
use Misago\ActiveSupport;
use Misago\ActiveSupport\String;
use Misago\I18n;

# Database object abstraction.
# 
# Permits to handle database entries like objects. It supports
# CRUD operations (<tt>create</tt>, read, <tt>update</tt>
# and <tt>delete</tt>).
# 
# Extends:
# 
# - <tt>Misago\ActiveRecord\Record</tt>
# - <tt>Misago\ActiveRecord\Associations</tt>
# - <tt>Misago\ActiveRecord\Calculations</tt>
# - <tt>Misago\ActiveRecord\Validations</tt>
# 
# =CRUD
# 
# All the examples will use this single model:
# 
#   class Post extends Misago\ActiveRecord\Base
#   {
#   }
# 
# ==Create
# 
# You can create a new record, then save it:
# 
#   $post = new Post(array('title' => 'aaa', 'body' => 'bbb'));
#   $post->save();
# 
# Or you can create it directly:
#   
#   $new_post = Post::create(array('title' => 'aaa', 'body' => 'bbb'));
# 
# 
# ==Read
# 
# ===Find one
# 
# All the following methods will return a single post. As a matter of fact,
# they all return the same post (in these examples):
# 
#   $post = new Post(1);
#   $post = Post::find(1);
#   $post = Post::find(':first', array('conditions' => array('id' => 1)));
#   $post = Post::find(':first', array('conditions' => 'id = 1'));
#   $post = Post::find_by_id(1);
# 
# If no post is found, returns null.
# 
# ===Find all
# 
# The following methods will return a collection of posts:
# 
#   $posts = Post::find();
#   $posts = Post::find(':all');
#   $posts = Post::find(':all', array('order' => 'created_at desc', 'limit' => 25));
#   $posts = Post::find_all_by_category('aaa');
#   $posts = Post::find_all_by_category('aaa', array('order' => 'title asc'));
# 
# If no posts are found, returns an empty <tt>ActiveArray</tt>.
# 
# ===Find values
# 
# Data won't be processed into objects, and it shall return a simple hash
# of key => value pairs. It's especially useful for collecting data for a
# HTML select. for instance
# 
#   $values = Post::find(':first', array('select' => 'id, title'));
#   # => array('id' => '1', 'title' => 'my post')
# 
# 
# ===Scopes
# 
# Scopes are predefined options for <tt>find</tt> requests.
# 
# ====Default scope
# 
# You may define a default scope for all finds. In the following example,
# all find requests to Comment will be returned ordered by creation date:
# 
#   class Comment extends Misago\ActiveRecord\Base
#   {
#     protected static $default_scope = array('order' => 'created_at asc');
#   }
# 
# Attention:
# 
# - once a default scope has been defined all find requests will be affected. This can be troublesome sometimes.
# - the default scope also affects the 'include' option, which shall be pretty convenient.
# 
# 
# ====Named scopes
# 
# [TODO]
# 
# 
# ==Update
# 
# There are several ways to update a record.
# 
#   $updated_post = Post::update(3, array('category' => 'ccc'));
#   
#   $post = new Post(2);
#   $post->title = 'abcd';
#   $post->save();
#   
#   $post = new Post(4);
#   $post->update_attributes(array('category' => 'ddd'));
#   
# Check <tt>update</tt>, <tt>update_attribute</tt> and <tt>update_attributes</tt>
# for more examples.
# 
# 
# ==Delete
# 
# There are two ways to delete records: <tt>delete</tt> and <tt>destroy</tt>.
# 
# The difference is that <tt>destroy</tt> always instanciates the record
# before deletion, permitting to interact with it. To delete an
# uploaded photo when deleting an image from a web gallery for
# instance.
# 
# On the contrary, <tt>delete</tt> will delete all records at once in
# the database throught a SQL +DELETE+ statement. There is no way to interact
# with the deletion of a particular entry.
# 
# The advantage of <tt>destroy</tt> is the ability to interact with the
# deletion; while the advantage of <tt>delete</tt> is it should be faster,
# especially when deleting many records.
# 
# Implementation detail: there is actually no +delete+ and +destroy+ methods,
# since PHP 5 disallows +$this+ in a static method, which disallows to use the
# same method for a static and an instance call. The actual methods are
# <tt>_delete</tt>, <tt>_static_delete</tt>, <tt>_destroy</tt> and
# <tt>_static_destroy</tt>. You must *never* call them directly.
# 
# ===delete
#
#   Post::delete(5);
# 
#   $post = new Post();
#   $post->delete(3);
# 
#   Post::delete_all(array('category' => 'aaa'));
#   Post::delete_all(array('category' => 'bbb', array('limit' => 5, 'order' => 'created_at desc'));
# 
# ===destroy
# 
#   Post::destroy(5);
# 
#   $post = new Post();
#   $post->destroy(3);
# 
#   $post = new Post();
#   Post::destroy_all(array('category' => 'aaa'));
#   Post::destroy_all(array('category' => 'bbb', array('limit' => 5, 'order' => 'created_at desc'));
# 
# ==Callbacks
# 
# Callbacks are hooks inside the lifecycle of an action to the record.
# 
# For instance when saving a new record:
# 
# - save()
# - is_valid()
# - [1] before_validation()
# - [2] before_validation_on_create()
# - validation()
# - validation_on_create()
# - [3] after_validation_on_create()
# - [4] after_validation()
# - [5] before_save()
# - [6] before_create()
# - create()
# - [7] after_create()
# - [8] after_save()
# 
# As you can see, there are a lot of callbacks, which permits you to
# interact with the creation process at every step of it. Same goes
# for <tt>update</tt>, which has the same lifecycle, but uses particular
# +*_on_update+ callbacks instead of +*_on_create+.
# 
# <tt>destroy</tt> has callbacks too, but the lifecycle is simplier:
# 
# - destroy()
# - [1] before_destroy()
# - *actually deletes the entry*
# - [2] after_destroy()
# 
# Remember that only <tt>destroy</tt> has callbacks, <tt>delete</tt> has no such handlers.
# 
# 
# IMPROVE: Check if columns do not conflict with object class attributes.
# TEST: Test callbacks.
# 
abstract class Base extends Calculations
{
  # The connection to the database (see <tt>Misago\ActiveRecord\Connection</tt>).
  protected static $connection;
  protected static $table_name;
  protected static $primary_key;
  protected static $default_scope = array();
  
  protected $new_record = true;
  
  function __construct($arg=null)
  {
    \Misago\Object::__construct();
    
    if ($arg !== null)
    {
      if (!is_array($arg))
      {
        $arg = static::find($arg);
        if ($arg !== null) {
          $this->new_record = false;
        }
      }
      Record::__construct($arg);
    }
  }
  
  
  function __set($attribute, $value)
  {
    if (static::has_column($attribute))
    {
      if ($value !== null)
      {
        $column = static::column_for_attribute($attribute);
        switch($column['type'])
        {
          case 'integer': $value = (int)$value;    break;
          case 'float':   $value = (double)$value; break;
          case 'boolean': $value = (bool)$value;   break;
          case 'datetime':
            if (!($value instanceof ActiveSupport\Datetime)) {
              $value = new ActiveSupport\Datetime($value);
            }
          break;
          case 'date':
            if (!($value instanceof ActiveSupport\Date)) {
              $value = new ActiveSupport\Date($value);
            }
          break;
          case 'time':
            if (!($value instanceof ActiveSupport\Time)) {
              $value = new ActiveSupport\Time($value);
            }
          break;
        }
      }
    }
    return parent::__set($attribute, $value);
  }
  
  static function connection()
  {
    if (!isset(static::$connection)) {
      static::$connection = Connection::get($_SERVER['MISAGO_ENV']);
    }
    return static::$connection;
  }
  
  static function primary_key() {
    return isset(static::$primary_key) ? static::$primary_key : 'id';
  }
  
  static function table_name()
  {
    return isset(static::$table_name) ? static::$table_name :
      String::underscore(String::pluralize(get_called_class()));
  }
  
  function new_record() {
    return $this->new_record;
  }
  
  # Always returns the record's primary key value, whatever if it's named id or not.
  function id() {
    return $this->{$this->primary_key};
  }
  
  # :nodoc:
  function id_set($value) {
    return $this->{$this->primary_key} = $value;
  }
  
  # Returns the list of columns with definitions.
  static function columns()
  {
    if (!isset(self::$_columns[get_called_class()]))
    {
      $apc_key = TMP.'/cache/active_records/columns_'.static::table_name();
      $columns = apc_fetch($apc_key, $success);
      if ($success === false)
      {
        $columns = static::connection()->columns(static::table_name());
        apc_store($apc_key, $columns);
      }
      static::$_columns[get_called_class()] = $columns;
    }
    return static::$_columns[get_called_class()];
  }
  
  # Returns the I18n translation of model name
  # (in +active_record.models+ context).
  # Defaults to the <tt>Misago\ActiveSupport\String::humanize()</tt> method.
  static function human_name()
  {
    $model = String::underscore(get_called_class());
    $human_name = I18n::translate($model, array('context' => "active_record.models"));
    return String::humanize($human_name);
  }
  
  # Returns the I18n translation of attribute name
  # (in +active_record.attributes.$model+ context).
  # Defaults to the <tt>Misago\ActiveSupport\String::humanize()</tt> method.
  static function human_attribute_name($attribute)
  {
    $model = String::underscore(get_called_class());
    $human_name = I18n::translate($attribute, array('context' => "active_record.attributes.$model"));
    return String::humanize($human_name);
  }
  
  # Declares some magic methods.
  # 
  # Examples:
  # 
  #   $user  = User::find_by_id($id);
  #   $post  = Post::find_first_by_tag($tag);
  #   $posts = Post::find_all_by_category_id($category_id);
  # 
  #   $count_users = User::count_by_id($id);
  # 
  static function __callStatic($method, $args)
  {
    if (preg_match('/^(find|count)(?:_([^_]+)|)(?:_by_(.+)|)$/', $method, $match))
    {
      if (!empty($match[3]))
      {
        if (!in_array($match[3], static::column_names()))
        {
          trigger_error("No such column '{$match[3]}'.", E_USER_WARNING);
          return;
        }
        if (!isset($args[0]))
        {
          trigger_error("Missing parameter: 'value'.", E_USER_WARNING);
          return;
        }
        $options = isset($args[1]) ? $args[1] : array();
        $options['conditions'] = array($match[3] => $args[0]);
      }
      else {
        $options = isset($args[0]) ? $args[0] : array();
      }
      
      if ($match[1] == 'find')
      {
        $scope = empty($match[2]) ? ':first' : ':'.$match[2];
        return static::find($scope, $options);
      }
      else {
        return static::count($options);
      }
    }
    elseif ($method == 'delete') {
      return forward_static_call_array(array(get_called_class(), '_static_delete'), $args);
    }
    elseif ($method == 'destroy') {
      return forward_static_call_array(array(get_called_class(), '_static_destroy'), $args);
    }
    return parent::__callStatic($method, $args);
  }
  
  function __call($method, $args)
  {
    if ($method == 'delete') {
      return call_user_func_array(array($this, '_delete'), $args);
    }
    elseif ($method == 'destroy') {
      return call_user_func_array(array($this, '_destroy'), $args);
    }
    return parent::__call($method, $args);
  }
  
  # Finds records in database.
  # 
  # Methods:
  # 
  # - +:all+    - Returns all found records.
  # - +:first+  - Returns the first found record (null if nothing is found).
  # - +:values+ - Returns bare values (uninstanciated).
  # 
  # Options:
  # 
  # - +select+ - (collection)
  # - +conditions+ - (string, array or hash)
  # - +group+ - (collection)
  # - +order+ - (collection)
  # - +limit+ - (integer)
  # - +page+ - (integer)
  # - +include+ - (collection)
  # 
  # Eager Loading:
  # 
  # See <tt>Misago\ActiveRecord\Associations</tt>.
  # 
  # TEST: Test option 'group'.
  static function find($method_or_id=':all', $options=null)
  {
    # arguments
    if (!is_symbol($method_or_id))
    {
      if (is_array($method_or_id) and !is_array($options))
      {
        $options =& $method_or_id;
        $method = ':all';
      }
      else
      {
        $options = array(
          'conditions' => array(static::primary_key() => $method_or_id),
        );
        $method = ':first';
      }
    }
    else {
      $method = $method_or_id;
    }
    
    # default scope
    $options = is_array($options) ?
      hash_merge_recursive(static::default_scope(), $options) :
      static::default_scope();
    
    # optimization(s)
    if ($method == ':first' and !isset($options['limit'])) {
      $options['limit'] = 1;
    }
    
    # queries then creates objects
    $sql = static::build_sql_from_options($options);
    
    switch($method)
    {
      case ':all':
        $results = static::connection()->select_all($sql);
        $records = array();
        foreach($results as $result)
        {
          $record = new static($result);
          $record->new_record = false;
          $records[] = $record;
        }
        $records = new ActiveSupport\ActiveArray($records, get_called_class());
        if (!empty($options['include'])) {
          static::eager_loading($records, $options['include']);
        }
        return $records;
      break;
      
      case ':first':
        $result = static::connection()->select_one($sql);
        if ($result)
        {
          $record = new static($result);
          $record->new_record = false;
        }
        else {
          $record = null;
        }
        return $record;
      break;
      
      case ':values':
        $results = static::connection()->select_all($sql);
        foreach($results as $i => $values) {
          $results[$i] = array_values($results[$i]);
        }
        return $results;
      break;
    }
  }
  
  static function default_scope()
  {
    return static::$default_scope;
  }
  
  # Shortcut for <tt>find</tt>(:all).
  static function all($options=null)
  {
    return static::find(':all', $options);
  }
  
  # Shortcut for <tt>find</tt>(:first).
  static function first($options=null)
  {
    return static::find(':first', $options);
  }
  
  # Shortcut for <tt>find</tt>(:values).
  static function values($options=null)
  {
    return static::find(':values', $options);
  }
  
  # Returns an array of ActiveRecords, using results as attributes.
  # 
  # Use <tt>find</tt> instead, unless you need special features. Be aware that
  # custom SQL requests may brake whenever you switch between
  # connection adapters.
  static function & find_by_sql($sql)
  {
    $rows = static::connection()->select_all($sql);
    foreach(array_keys($rows) as $i) {
      $rows[$i] = new static($rows[$i]);
    }
    return $rows;
  }
  
  # Counts columns. SQL Request is supposed to return a single column
  # and a single row (returns a single int).
  # 
  #   $count = $post->count_by_sql("select count(*) from posts where created_at > now()");
  # 
  # Use <tt>count</tt> instead, unless you need special features. Be aware
  # that custom SQL requests may brake whenever you switch between
  # connection adapters.
  static function count_by_sql($sql)
  {
    $rows = static::connection()->select_values($sql);
    return (int)$rows[0];
  }
  
  # Checks wether a given record exists or not.
  static function exists($id)
  {
    if (empty($id) and strlen($id) == 0) {
      return false;
    }
    $options = array('conditions' => array(static::primary_key() => $id));
    return (bool)static::count($options);
  }
  
  # Returns full SQL string from given options.
  static function build_sql_from_options($options)
  {
    $connection = static::connection();
    
    # builds SQL
    $table  = $connection->quote_table(static::table_name());
    $select = empty($options['select']) ? '*' : $connection->quote_columns($options['select']);
    $where  = '';
    $group  = '';
    $order  = '';
    $limit  = '';
    $joins  = '';
    
    if (!empty($options['joins']))
    {
      $joins = is_array($options['joins']) ? $options['joins'] : array($options['joins']);
      foreach($joins as $i => $join)
      {
        if (static::has_association($join)) {
          $joins[$i] = static::build_join_for($join);
        }
      }
      $joins = implode(' ', $joins);
    }
    if (!empty($options['conditions'])) {
      $where = 'WHERE '.$connection->sanitize_sql_for_conditions($options['conditions']);
    }
    if (!empty($options['group'])) {
      $group = 'GROUP BY '.$connection->sanitize_order($options['group']);
    }
    if (!empty($options['order'])) {
      $order = 'ORDER BY '.$connection->sanitize_order($options['order']);
    }
    if (isset($options['limit']))
    {
      $page  = isset($options['page']) ? $options['page'] : null;
      $limit = $connection->sanitize_limit($options['limit'], $page);
    }
    
    return "SELECT $select FROM $table $joins $where $group $order $limit";
  } 
  
  static function merge_conditions($a, $b)
  {
    if (!empty($a) and empty($b)) {
      return $a;
    }
    if (empty($a) and !empty($b)) {
      return $b;
    }
    $connection = static::connection();
    $a = $connection->sanitize_sql_for_conditions($a);
    $b = $connection->sanitize_sql_for_conditions($b);
    return "($a) AND ($b)";
  }
  
  static function & merge_options($a, $b)
  {
    $c = array_merge($a, $b);
    if (!empty($a['conditions']) and !empty($b['conditions'])) {
      $c['conditions'] = static::merge_conditions($a['conditions'], $b['conditions']);
    }
    return $c;
  }
  
  # Executes a function inside a database transaction.
  # 
  # Whenever an exception is raised, transacted queries
  # will be rollbacked and it returns false.
  # 
  # If no exception is raised, transacted queries will be
  # commited to the database, and it returns the executed
  # function's result.
  static function transaction($callback, array $args=array())
  {
    $connection = static::connection();
    
    if (is_string($callback)) {
      $callback = array($this, $callback);
    }
    $connection->transaction('begin');
    
    try {
      $rs = call_user_func_array($callback, $args);
    }
    catch(Exception $e)
    {
      $connection->transaction('rollback');
      return false;
    }
    
    $connection->transaction('commit');
    return $rs;
  }
  
  # Saves the record.
  function save($perform_validation=true)
  {
    if ($perform_validation) {
      return $this->save_with_validation();
    }
    return $this->save_without_validation();
  }
  
  # Saves the record, but throws an exception on error.
  function do_save($perform_validation=true)
  {
    if (!$this->save($perform_validation)) {
      throw new RecordNotSaved('Record was not saved.');
    }
    return true;
  }
  
  # Generic create record method.
  # 
  # You better consider this method as private.
  # Do not use unless you know what you are doing (ie. you're hacking misago).
  # Use <tt>create</tt> instead.
  # 
  # :nodoc:
  protected function _create()
  {
    $this->before_save();
    $this->before_create();
    
    # timestamps
    $column_names = static::column_names();
    if (in_array('created_at', $column_names) and empty($this->created_at)) {
      $this->created_at = new ActiveSupport\Datetime();
    }
    if (in_array('created_on', $column_names) and empty($this->created_on)) {
      $this->created_on = new ActiveSupport\Date();
    }
    
    # create
    $attributes = $this->attributes();
    $id = static::connection()->insert(static::table_name(), $attributes, static::primary_key());
    if ($id)
    {
      $this->new_record = false;
      if ($this->id === null) {
        $this->id = $id;
      }
      
      if (!$this->save_associated()) {
        return false;
      }
      
      $this->after_create();
      $this->after_save();
      
      # dirty object:
      $this->reset_original_attributes();
      
      return $id;
    }
    return false;
  }
  
  # Generic update record method.
  # 
  # You better consider this method as private.
  # Do not use unless you know what you are doing (ie. you're hacking misago).
  # Use <tt>update</tt> or <tt>update_attributes</tt> instead.
  # 
  # :nodoc:
  protected function _update()
  {
    $this->before_save();
    $this->before_update();
    
    # nothing changed?
    if (!$this->changed) {
      return true;
    }
    
    # timestamps
    $column_names = static::column_names();
    if (in_array('updated_at', $column_names) and empty($this->updated_at)) {
      $this->updated_at = new ActiveSupport\Datetime();
    }
    if (in_array('updated_on', $column_names) and empty($this->updated_on)) {
      $this->updated_on = new ActiveSupport\Date();
    }
    
    # update
    $conditions = array(static::primary_key() => $this->id);
    $updates = $this->changes();
    if (empty($updates)) {
      return true;
    }
    $rs = static::connection()->update(static::table_name(), $updates, $conditions);
    
    if ($rs !== false)
    {
      if (!$this->save_associated()) {
        return false;
      }
      
      $this->after_update();
      $this->after_save();
      
      # dirty object:
      $this->reset_original_attributes();
      
      return $rs;
    }
    return false;
  }
  
  # Creates a new record.
  # 
  #   $user  = User::create(array('name' => 'John'));
  #   $users = User::create(array('name' => 'Jane'), array('name' => 'Billy'));
  static function create(array $attributes)
  {
    if (func_num_args() == 1)
    {
      $record = new static($attributes);
      $record->save();
      return $record;
    }
    else
    {
      $static = get_called_class();
      $args   = func_get_args();
      
      return static::transaction(function() use($static, $args)
      {
        $records = array();
        foreach($args as $attributes) {
          $records[] = $static::do_create($attributes);
        }
        return $records;
      });
    }
    return false;
  }
  
  # Same as <tt>create</tt> but throws an exception on failure.
  static function do_create(array $attributes)
  {
    $args = func_get_args();
    $rs   = forward_static_call_array(array(get_called_class(), 'create'), $args);
    if ($rs === false) {
      throw new RecordNotSaved;
    }
    return $rs;
  }
  
  # Updates one or many records.
  # 
  #   # update one row
  #   $user = $user->update(1, array('name' => 'Joe'));
  #   
  #   # update many rows
  #   $people = array(
  #     1 => array('name' => 'Polly'),
  #     2 => array('name' => 'Jean')
  #   );
  #   $users = User::update(array_keys($people), array_values($people));
  static function update($id, $attributes)
  {
    if (!is_array($id))
    {
      $record = new static($id);
      $record->update_attributes($attributes);
      return $record;
    }
    else
    {
      $static = get_called_class();
      return static::transaction(function($ids, $attributes) use ($static)
      {
        $records = array();
        foreach($ids as $i => $id)
        {
          $records[] = $static::do_update($id, $attributes[$i]);
          $i++;
        }
        return $records;
      }, array($id, $attributes));
    }
  }
  
  # Same as <tt>update</tt> but throws an exception on failure.
  static function do_update($id, $attributes)
  {
    $rs = static::update($id, $attributes);
    if ($rs === false) {
      throw new RecordNotSaved;
    }
    return $rs;
  }
  
  # Updates many records at once, and returns the number of affected rows.
  # 
  # Available options: limit, order.
  static function update_all($updates, $conditions=null, $options=null)
  {
    $options['primary_key'] = static::primary_key();
    return static::connection()->update(static::table_name(), $updates, $conditions, $options);
  }
  
  # Updates a single attribute of record, without going throught
  # the validation process.
  # 
  #   $post->update_attribute('name', 'my first post [update]');
  function update_attribute($attribute, $value)
  {
    $this->$attribute = $value;
    return $this->save(false);
  }
  
  # Updates some attributes of record.
  # 
  #   $post = new Post(1);
  #   $post->title    = 'my first post [update]';
  #   $post->category = 2;
  #   $post->update_attributes(array('title', 'category'));
  #   $post->update_attributes('title, category');
  #   
  #   $post->update_attributes(array(
  #     'title'    => 'my first post [update 2]',
  #     'category' => 3
  #   ));
  function update_attributes($updates)
  {
    $this->attributes = $updates;
    return $this->save();
  }
  
  # Toggles a boolean from true to false (or false to true).
  function toggle($attribute)
  {
    $column = static::column_for_attribute($attribute);
    if ($column['type'] != 'boolean') {
      throw new RecordNotSaved("Cannot toggle $attribute: not a boolean column.");
    }
    return $this->update_attribute($attribute, !$this->$attribute);
  }
  
  # Deletes the record from database using a SQL +DELETE+ statement.
  # The record isn't instanciated, and callbacks aren't runned. This
  # is faster than the +destroy+ method.
  # 
  #   Post::delete(1);
  #   Post::delete(array(1, 2, 3));
  # 
  static function _static_delete($id)
  {
    $conditions = array(static::primary_key() => $id);
    return static::connection()->delete(static::table_name(), $conditions);
  }
  
  # Deletes the record from database using a SQL +DELETE+ statement.
  # The record isn't instanciated, and callbacks aren't runned. This
  # is faster than the +destroy+ method.
  # 
  #   $post = new Post(456);
  #   $post->delete();       # => delete from posts where id = 456;
  # 
  function _delete()
  {
    if (!$this->new_record)
    {
      $conditions = array(static::primary_key() => $this->id);
      return static::connection()->delete(static::table_name(), $conditions);
    }
    return true;
  }
  
  # :nodoc:
  function do_delete()
  {
    if (!$this->delete()) {
      throw new Exception();
    }
  }
  
  # Deletes many records at once using a single SQL +DELETE+ statement. Records
  # aren't instanciated and deletion callbacks aren't runned. This is faster
  # than the <tt>destroy_all</tt> method.
  # 
  # Available options: +limit+, +order+
  static function delete_all($conditions=null, $options=null)
  {
    $options['primary_key'] = static::primary_key();
    return static::connection()->delete(static::table_name(), $conditions, $options);
  }
  
  # Deletes the record from database. The record is instanciated and callbacks
  # are processed. This is slower than the +delete+ method.
  # 
  #   Post::destroy(1);
  # 
  static function _static_destroy($id)
  {
    $record = new static($id);
    
    if (!$record->new_record)
    {
      $record->before_destroy();
      $record->delete_associated();
      
      $conditions = array(static::primary_key() => $record->id);
      if (!static::connection()->delete(static::table_name(), $conditions)) {
        return false;
      }
      
      $record->after_destroy();
    }
    return true;
  }
  
  # Deletes the record from database. The record is instanciated, and callbacks
  # (<tt>before_destroy</tt>, <tt>after_destroy</tt>) are processed. This is slower
  # than the <tt>delete</tt> method, but it permits to interact with the
  # deletion (ie. do some cleanup).
  # 
  #   $post = new Post(456);
  #   $post->destroy();
  # 
  function _destroy()
  {
    if (!$this->new_record)
    {
      $this->before_destroy();
      $this->delete_associated();

      $conditions = array(static::primary_key() => $this->id);
      if (!static::connection()->delete(static::table_name(), $conditions)) {
        return false;
      }
      
      $this->after_destroy();
    }
    return true;
  }
  
  # :nodoc:
  function do_destroy()
  {
    if (!$this->destroy()) {
      throw new Exception();
    }
  }
  
  # Destroys many records at once. Each matching record is instanciated, and
  # deletion callbacks (<tt>before_destroy</tt>, <tt>after_destroy</tt>)are run.
  # This is slower than the <tt>delete_all</tt> method, but it permits to clean
  # up things.
  # 
  # Available options: +limit+, +order+
  static function destroy_all($conditions=null, $options=null)
  {
    if (!empty($conditions)) {
      $options['conditions'] = $conditions;
    }
    
    $sql = static::build_sql_from_options($options);
    $ids = static::connection()->select_values($sql);
    
    foreach($ids as $id)
    {
      if (!static::destroy($id[0])) {
        return false;
      }
    }
    return true;
  }
  
  # Generates a cache key for this record.
  # 
  # Produces +class_name/id-updated_at+ if +update_at+ exists,
  # otherwise falls back to +class_name/id+.
  function cache_key()
  {
    if ($this->new_record) {
      return String::underscore(String::pluralize(get_called_class())).'/new';
    }
    elseif (static::has_column('updated_at')) {
      return String::underscore(get_called_class()).'/'.$this->id.'-'.$this->updated_at->to_s('number');
    }
    elseif (static::has_column('updated_on')) {
      return String::underscore(get_called_class()).'/'.$this->id.'-'.$this->updated_on->to_s('number');
    }
    else {
      return String::underscore(get_called_class()).'/'.$this->id;
    }
  }
  
  protected function before_save()   {}
  protected function after_save()    {}
  
  protected function before_create() {}
  protected function after_create()  {}
  
  protected function before_update() {}
  protected function after_update()  {}

  protected function before_destroy() {}
  protected function after_destroy()  {}
}

?>
