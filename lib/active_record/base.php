<?php

# Database object abstraction.
# 
# Permits to handle database entries like objects. It supports
# CRUD operations (create, read, update and delete), validations
# through ActiveRecord::Associations, and relations through
# ActiveRecord::Associations.
# 
# =CRUD
# 
# All the examples will use this single model:
# 
#   class Post extends ActiveRecord_Base {
#     
#   }
# 
# ==Create
# 
# There are two ways to create a new record. You either save an already
# built object, or you create it directly.
# 
#   $post = new Post(array('title' => 'aaa', 'body' => 'bbb'));
#   $post->save();
#   
#   $post = new Post();
#   $new_post = $post->create(array('title' => 'aaa', 'body' => 'bbb'));
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
#   $post = $post->find(1);
#   $post = $post->find(':first', array('conditions' => array('id' => 1)));
#   $post = $post->find(':first', array('conditions' => 'id = 1'));
#   $post = $post->find_by_id(1);
# 
# ===Find all
# 
# The following methods will return a collection of posts:
# 
#   $post  = new Post();
#   $posts = $post->find();
#   $posts = $post->find(':all');
#   $posts = $post->find(':all', array('order' => 'created_at desc', 'limit' => 25));
#   $posts = $post->find_all_by_category('aaa');
#   $posts = $post->find_all_by_category('aaa', array('order' => 'title asc'));
# 
# ===Scopes
# 
# Scopes are predefined options for find requests.
# 
# 
# ====Default scope
# 
# You may define a default scope for all finds. In the following example,
# all find requests to Comment will be returned ordered by creation date:
# 
#   class Comment {
#     protected $default_scope = array('order' => 'created_at asc');
#   }
# 
# Attention:
# 
# - once a default scope has been defined all find requests will be
#   affected. This could be troublesome, sometimes.
# - the default scope also affects the 'include' option, which shall
#   be pretty convenient.
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
#   $post = new Post();
#   $updated_post = $post->update(3, array('category' => 'ccc'));
#   
#   $post = new Post(2);
#   $post->title = 'abcd';
#   $post->save();
#   
#   $post = new Post(4);
#   $post->update_attributes(array('category' => 'ddd'));
#   
# Check update(), update_attribute() and update_attributes() for
# more examples.
# 
# 
# ==Delete
# 
# There are two ways to delete records: delete or destroy.
# 
# The difference is that delete always instanciates the record
# before deletion, permitting to interact with it. To delete an
# uploaded photo when deleting an image from a web gallery for
# instance.
# 
# On the contrary, destroy will delete all records at once in
# the database. There is no way to interact with the deletion
# of a particular entry.
# 
# The advantage of delete is to be able to interact with the
# deletion, but the advantage of destroy is it should be faster,
# especially when deleting many records.
# 
# ===delete
#
#   $post = new Post(5);
#   $post->delete();
# 
#   $post = new Post();
#   $post->delete(3);
# 
#   $post = new Post();
#   $post->delete_all(array('category' => 'aaa'));
#   $post->delete_all(array('category' => 'bbb', array('limit' => 5, 'order' => 'created_at desc'));
# 
# ===destroy
# 
#   $post = new Post(5);
#   $post->destroy();
# 
#   $post = new Post();
#   $post->destroy(3);
# 
#   $post = new Post();
#   $post->destroy_all(array('category' => 'aaa'));
#   $post->destroy_all(array('category' => 'bbb', array('limit' => 5, 'order' => 'created_at desc'));
# 
# ==Callbacks
# 
# Callbacks are hooks inside the lifecycle of an action to the record.
# 
# For instance when saving a new record:
# 
# - save()
# - is_valid()
# - before_validation()
# - before_validation_on_create()
# - validation()
# - validation_on_create()
# - after_validation_on_create()
# - after_validation()
# - before_save()
# - before_create()
# - create()
# - after_create()
# - after_save()
# 
# As you can see, there is a lot of callbacks, which permits you to
# interact with the creation process at every step of it. Same goes
# for update, which as the same lifecycle, but uses particular
# `on_update` callbacks instead of `on_create`.
# 
# Delete has callbacks too. But the lifecycle is simplier:
# 
# - delete()
# - before_delete()
# - *actually deletes the entry*
# - after_delete()
# 
# Remember that only delete has callbacks, destroy has no such methods.
# 
# 
# @package ActiveRecord
# 
# TODO: Implement calculations.
# TODO: Named scopes.
# IMPROVE: Test callbacks.
# 
abstract class ActiveRecord_Base extends ActiveRecord_Validations
{
  protected $db;
  protected $table_name;
  protected $primary_key   = 'id';
  protected $columns       = array();
  protected $default_scope = array();
  
  # IMPROVE: Check if columns do not conflict with object class attributes.
  function __construct($arg=null)
  {
    # database connection
    if (empty($this->table_name)) {
      $this->table_name = String::underscore(String::pluralize(get_class($this)));
    }
    $this->db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    
    # columns' definition
    $apc_key = TMP.'/cache/active_records/columns_'.$this->table_name;
    $this->columns = apc_fetch($apc_key, $success);
    if ($success === false)
    {
      $this->columns = $this->db->columns($this->table_name);
      apc_store($apc_key, $this->columns);
    }
    
    # primary key
    foreach($this->columns as $attribute => $def)
    {
      if ($def['primary_key'])
      {
        $this->primary_key = $attribute;
        break;
      }
    }
    
    # relationships
    ActiveRecord_Associations::__construct();
    
    # args
    if ($arg !== null)
    {
      if (!is_array($arg))
      {
        $arg = $this->find($arg);
        $this->new_record = false;
      }
      $this->set_attributes($arg);
    }
  }
  
  function __set($attribute, $value)
  {
    if (isset($this->columns[$attribute]))
    {
      if ($value !== null)
      {
        switch($this->columns[$attribute]['type'])
        {
          case 'integer': $value = (int)$value;    break;
          case 'double':  $value = (double)$value; break;
          case 'bool':    $value = (bool)$value;   break;
          case 'date': case 'datetime': case 'time':
            if (!($value instanceof Time)) {
              $value = new Time($value);
            }
          break;
        }
      }
      return parent::__set($attribute, $value);
    }
    elseif ($attribute == 'id') {
      return $this->id = parent::__set($this->primary_key, $value);
    }
    else {
      return $this->$attribute = $value;
    }
  }
  
  function __get($attribute)
  {
    if ($attribute == 'id' and !isset($this->columns['id'])) {
      $attribute = $this->primary_key;
    }
    return parent::__get($attribute);
  }
  
  private function & _get_attributes()
  {
    $attributes = $this->__attributes;
    foreach(array_keys($attributes) as $k)
    {
      if (is_object($attributes[$k]) and method_exists($attributes[$k], 'to_s')) {
        $attributes[$k] = $attributes[$k]->to_s('db');
      }
    }
    return $attributes;
  }
  
  # Returns the I18n translation of model name
  # (in active_record.models context).
  # Defaults to the String::humanize() method.
  function human_name()
  {
    $model = String::underscore(get_class($this));
    $human_name = I18n::translate($model, array('context' => "active_record.models"));
    return String::humanize($human_name);
  }
  
  # Returns the I18n translation of attribute name
  # (in active_record.attributes.$model context).
  # Defaults to the String::humanize() method.
  function human_attribute_name($attribute)
  {
    $model = String::underscore(get_class($this));
    $human_name = I18n::translate($attribute, array('context' => "active_record.attributes.$model"));
    return String::humanize($human_name);
  }
  
  /**
   * Declares some magic methods.
   * 
   * Examples:
   * 
   *   $user  = $user->find_by_id();
   *   $post  = $post->find_first_by_tag($tag);
   *   $posts = $post->find_all_by_category_id($category_id);
   */
  function __call($func, $args)
  {
    if (preg_match('/^find(?:_([^_]+)|)(?:_by_(.+)|)$/', $func, $match))
    {
      if (!empty($match[2]))
      {
        if (!in_array($match[2], array_keys($this->columns)))
        {
          trigger_error("No such column '{$match[2]}'.", E_USER_WARNING);
          return;
        }
        if (!isset($args[0]))
        {
          trigger_error("Missing parameter: 'value'.", E_USER_WARNING);
          return;
        }
        $options = isset($args[1]) ? $args[1] : array();
        $options['conditions'] = array($match[2] => $args[0]);
      }
      else {
        $options = isset($args[0]) ? $args[0] : array();
      }
      
      $method = empty($match[1]) ? ':first' : ':'.$match[1];
      return $this->find($method, $options);
    }
    return parent::__call($func, $args);
  }
  
  /**
   * Finds records in database.
   * 
   * Methods:
   * 
   * - :all    Returns all found records.
   * - :first  Returns the first found record.
   * - :values Returns bare values (uninstanciated).
   * 
   * Options:
   * 
   * - select (collection)
   * - conditions (string, array or hash)
   * - group (collection)
   * - order (collection)
   * - limit (integer)
   * - page (integer)
   * - include (collection)
   * 
   * Eager Loading:
   * 
   * See ActiveRecord_Associations.
   * 
   * TODO: Test option 'group'.
   */
  function find($method_or_id=':all', $options=null)
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
          'conditions' => array($this->primary_key => $method_or_id),
        );
        $method = ':first';
      }
    }
    else {
      $method = $method_or_id;
    }
    
    # default scope
    $options = is_array($options) ?
      hash_merge_recursive($this->default_scope, $options) : $this->default_scope;
    
    # optimization(s)
    if ($method == ':first' and !isset($options['limit'])) {
      $options['limit'] = 1;
    }
    
    # queries then creates objects
    $sql = $this->build_sql_from_options(&$options);
    
    $model = get_class($this);
    switch($method)
    {
      case ':all':
        $results = $this->db->select_all($sql);
        $records = array();
        foreach($results as $result)
        {
          $record = new $model($result);
          $record->new_record = false;
          $records[] = $record;
        }
        $records = new ActiveArray($records, $model);
        if (!empty($options['include'])) {
          $this->eager_loading($records, $options['include']);
        }
        return $records;
      break;
      
      case ':first':
        $result = $this->db->select_one($sql);
        if ($result)
        {
          $record = new $model($result);
          $record->new_record = false;
        }
        else {
          $record = null;
        }
        return $record;
      break;
      
      case ':values':
        $results = $this->db->select_all($sql);
        foreach($results as $i => $values) {
          $results[$i] = array_values($results[$i]);
        }
        return $results;
      break;
    }
  }
  
  protected function build_sql_from_options($options)
  {
    # builds SQL
    $table  = $this->db->quote_table($this->table_name);
    $select = empty($options['select']) ? '*' : $this->db->quote_columns($options['select']);
    $where  = '';
    $group  = '';
    $order  = '';
    $limit  = '';
    $joins  = '';
    
    if (!empty($options['joins'])) {
      $joins = is_array($options['joins']) ? implode(' ', $options['joins']) : $options['joins'];
    }
    if (!empty($options['conditions'])) {
      $where = 'WHERE '.$this->db->sanitize_sql_for_conditions($options['conditions']);
    }
    if (!empty($options['group'])) {
      $group = 'GROUP BY '.$this->db->sanitize_order($options['group']);
    }
    if (!empty($options['order'])) {
      $order = 'ORDER BY '.$this->db->sanitize_order($options['order']);
    }
    if (isset($options['limit']))
    {
      $page  = isset($options['page']) ? $options['page'] : null;
      $limit = $this->db->sanitize_limit($options['limit'], $page);
    }
    
    return "SELECT $select FROM $table $joins $where $group $order $limit ;";
  } 
  
  protected function merge_conditions($a, $b)
  {
    if (!empty($a) and empty($b)) {
      return $a;
    }
    if (empty($a) and !empty($b)) {
      return $b;
    }
    $a = $this->db->sanitize_sql_for_conditions($a);
    $b = $this->db->sanitize_sql_for_conditions($b);
    return "($a) AND ($b)";
  }
  
  protected function & merge_options($a, $b)
  {
    $c = array_merge_recursive($a, $b);
    if (!empty($a['conditions']) and !empty($b['conditions'])) {
      $c['conditions'] = $this->merge_conditions($a['conditions'], $b['conditions']);
    }
    return $c;
  }
  
  /**
   * Shortcut for find(:all).
   */
  function all($options=null)
  {
    return $this->find(':all', $options);
  }
  
  /**
   * Shortcut for find(:first).
   */
  function first($options=null)
  {
    return $this->find(':first', $options);
  }
  
  /**
   * Shortcut for find(:values).
   */
  function values($options=null)
  {
    return $this->find(':values', $options);
  }
  
  /**
   * Checks wether a given record exists or not.
   */
  function exists($id)
  {
    if (empty($id) and strlen($id) == 0) {
      return false;
    }
    $options = array(
      'conditions' => array($this->primary_key => $id),
      'select'     => $this->primary_key,
    );
    $self = $this->find(':first', $options);
    return (gettype($self) == 'object');
  }
  
  /**
   * Creates or updates the record.
   */
  function save()
  {
    $method = $this->new_record ? '_create' : '_update';
    return (bool)$this->$method();
  }
  
  # Generic create record method.
  # 
  # You better consider this method as private.
  # Do not use unless you know what you are doing (ie. you're hacking misago).
  # Use ActiveRecord_Base::create() instead.
  protected function _create()
  {
    if (!$this->is_valid()) {
      return false;
    }
    
    $this->before_save();
    $this->before_create();
    
    # timestamps
    if (array_key_exists('created_at', $this->columns) and empty($this->created_at)) {
      $this->created_at = new Time(null, 'datetime');
    }
    if (array_key_exists('created_on', $this->columns) and empty($this->created_on)) {
      $this->created_on = new Time(null, 'date');
    }
    
    # create
    $attributes = $this->_get_attributes();
    $id = $this->db->insert($this->table_name, $attributes, $this->primary_key);
    if ($id)
    {
      $this->new_record = false;
      $this->id = $id;
      
      $this->after_create();
      $this->after_save();
      
      return $id;
    }
    return false;
  }
  
  # Generic update record method.
  # 
  # You better consider this method as private.
  # Do not use unless you know what you are doing (ie. you're hacking misago).
  # Use ActiveRecord_Base::update() or ActiveRecord_Base::update_attributes() instead.
  protected function _update($attributes=null)
  {
    if ($attributes !== null)
    {
      foreach($attributes as $field => $value) {
        $this->$field = $value;
      }
    }
    
    if (!$this->is_valid()) {
      return false;
    }
    
    $this->before_save();
    $this->before_update();
    
    # timestamps
    if (array_key_exists('updated_at', $this->columns) and empty($this->updated_at)) {
      $this->updated_at = new Time(null, 'datetime');
    }
    if (array_key_exists('updated_on', $this->columns) and empty($this->updated_on)) {
      $this->updated_on = new Time(null, 'date');
    }
    
    # update
    $conditions = array($this->primary_key => $this->id);
    $updates = $this->_get_attributes();
    
    $rs = $this->db->update($this->table_name, $updates, $conditions);
    
    if ($rs !== false)
    {
      $this->after_update();
      $this->after_save();
      return $rs;
    }
    return false;
  }
  
  /**
   * Creates a new record.
   * 
   *   $user  = $user->create(array('name' => 'John'));
   *   $users = $user->create(array('name' => 'Jane'), array('name' => 'Billy'));
   */
  function create(array $attributes)
  {
    if (func_num_args() == 1)
    {
      $class  = get_class($this);
      $record = new $class($attributes);
      $record->_create();
      
      return $record;
    }
    else
    {
      $args    = func_get_args();
      $records = array();
      
      $this->db->transaction('begin');
      
      foreach($args as $attributes)
      {
        $record = $this->create($attributes);
        if ($record === false)
        {
          $this->db->transaction('rollback');
          return false;
        }
        $records[] = $record;
      }
      
      $this->db->transaction('commit');
      return $records;
    }
    return false;
  }
  
  /**
   * Updates one or many records.
   * 
   *   # update one row
   *   $user = $user->update(1, array('name' => 'Joe'));
   *   
   *   # update many rows
   *   $people = array(
   *     1 => array('name' => 'Polly'),
   *     2 => array('name' => 'Jean')
   *   );
   *   $users = $user->update(array_keys($people), array_values($people));
   */
  function update($id, $attributes)
  {
    if (!is_array($id))
    {
      $class = get_class($this);
      
      $record = new $class($id);
      $record->set_attributes($attributes);
      $attributes = array_intersect_key($attributes, $this->columns);
      $record->_update($attributes);
      
      return $record;
    }
    else
    {
      $records = array();
      $i = 0;
      
      $this->db->transaction('begin');
      
      foreach($id as $_id)
      {
        $rs = $this->update($_id, $attributes[$i]);
        
        if ($rs === false)
        {
          $this->db->transaction('rollback');
          return false;
        }
        $records[] = $rs;
        $i++;
      }

      $this->db->transaction('commit');
      return $records;
    }
  }
  
  /**
   * Updates one attribute of record.
   * 
   *   $post = new Post(1);
   *   $post->name = 'my first post [update]';
   *   $post->update_attribute('name');
   *   
   *   $post->update_attribute('name', 'my first post [update 2]');
   */
  function update_attribute($attribute, $value=null)
  {
    $value   = (func_num_args() > 1) ? $value : $this->$attribute;
    $updates = array($attribute => $value);
    return $this->update_attributes(&$updates);
  }
  
  /**
   * Updates some attributes of record.
   * 
   *   $post = new Post(1);
   *   $post->title    = 'my first post [update]';
   *   $post->category = 2;
   *   $post->update_attributes(array('title', 'category'));
   *   $post->update_attributes('title, category');
   *   
   *   $post->update_attributes(array(
   *     'title'    => 'my first post [update 2]',
   *     'category' => 3
   *   ));
   */
  function update_attributes($updates)
  {
    # hash of fields => values
    if (is_hash($updates)) {
      return $this->_update($updates);
    }
    
    # list of fields
    if (is_string($updates)) {
      $updates = explode(',', str_replace(' ', '', $updates));
    }
    $_updates = array();
    foreach($updates as $attribute) {
      $_updates[$attribute] = $this->$attribute;
    }
    return $this->_update(&$_updates);
  }
  
  /**
   * Updates many records at once.
   * 
   * Available options:
   * 
   * - limit
   * - order
   */
  function update_all($updates, $conditions=null, $options=null)
  {
    return $this->db->update($this->table_name, $updates, $conditions, $options);
  }
  
  /**
   * Deletes a record.
   * 
   *   # deletes a given record
   *   $post->delete(123);
   *   
   *   # deletes current record
   *   $post = new Post(456);
   *   $post->delete();
   */
  function delete($id=null)
  {
    $id = isset($id) ? $id : $this->id;
    
    if ($this->exists($id))
    {
      $class = get_class($this);
      $self  = new $class($id);
      $self->before_delete();
      
      $conditions = array($this->primary_key => $id);
      if (!$this->db->delete($this->table_name, $conditions)) {
        return false;
      }

      $self->after_delete();
      return true;
    }
  }
  
  /**
   * Deletes many records at once.
   * 
   * Each matching record are instanciated, and deletion callbacks are run.
   * 
   * Available options:
   * 
   * - limit
   * - order
   */
  function delete_all($conditions=null, $options=null)
  {
    if (!empty($conditions)) {
      $options['conditions'] = $conditions;
    }
    
    $sql = $this->build_sql_from_options($options);
    $ids = $this->db->select_values($sql);
    
    foreach($ids as $id)
    {
      if (!$this->delete($id[0])) {
        return false;
      }
    }
    return true;
  }
  
  # Destroys a record.
  # 
  # Record isn't instanciated, and deletion callbacks aren't run.
  function destroy($id=null)
  {
    if ($id === null) {
      $id = $this->id;
    }
    $conditions = array($this->primary_key => $id);
    return $this->db->delete($this->table_name, $conditions);
  }
  
  # Destroys many records at once.
  # 
  # Records aren't instanciated, and deletion callbacks aren't run.
  function destroy_all($conditions=null, $options=null)
  {
    return $this->db->delete($this->table_name, $conditions, $options);
  }
  
  # TODO: Test before_* and after_* callbacks.
  protected function before_save()   {}
  protected function after_save()    {}
  
  protected function before_create() {}
  protected function after_create()  {}
  
  protected function before_update() {}
  protected function after_update()  {}

  protected function before_delete() {}
  protected function after_delete()  {}
}

?>
