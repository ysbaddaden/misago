<?php

# Database object abstraction.
# 
# Permits to handle database entries like objects. It supports
# CRUD operations (create, read, update and delete), validations
# through ActiveRecord_Validations, and relations through
# ActiveRecord_Associations.
# 
# =CRUD
# 
# All the examples will use this single model:
# 
#   class Post extends ActiveRecord_Base
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
#   class Comment extends ActiveRecord_Base
#   {
#     protected $default_scope = array('order' => 'created_at asc');
#   }
# 
# Attention:
# 
# - once a default scope has been defined all find requests will be affected. This could be troublesome, sometimes.
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
# As you can see, there is a lot of callbacks, which permits you to
# interact with the creation process at every step of it. Same goes
# for update, which as the same lifecycle, but uses particular
# `on_update` callbacks instead of `on_create`.
# 
# Delete has callbacks too. But the lifecycle is simplier:
# 
# - delete()
# - [1] before_delete()
# - *actually deletes the entry*
# - [2] after_delete()
# 
# Remember that only delete has callbacks, destroy has no such methods.
# 
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
  
  protected $attr_read     = array('new_record', 'table_name');
  
  
  # IMPROVE: Check if columns do not conflict with object class attributes.
  function __construct($arg=null)
  {
    # database connection
    if (empty($this->table_name)) {
      $this->table_name = String::underscore(String::pluralize(get_class($this)));
    }
    $this->db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    
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
      ActiveRecord_Record::__construct($arg);
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
          case 'date':
          case 'datetime':
          case 'time':
            if (!($value instanceof Time)) {
              $value = new Time($value, $this->columns[$attribute]['type']);
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
  
  # Shortcut for find(:all).
  function all($options=null)
  {
    return $this->find(':all', $options);
  }
  
  # Shortcut for find(:first).
  function first($options=null)
  {
    return $this->find(':first', $options);
  }
  
  # Shortcut for find(:values).
  function values($options=null)
  {
    return $this->find(':values', $options);
  }
  
  # Checks wether a given record exists or not.
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
  
  function merge_conditions($a, $b)
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
  
  function & merge_options($a, $b)
  {
    $c = array_merge_recursive($a, $b);
    if (!empty($a['conditions']) and !empty($b['conditions'])) {
      $c['conditions'] = $this->merge_conditions($a['conditions'], $b['conditions']);
    }
    return $c;
  }
  
  # Executes a function inside a database transaction.
  # 
  # Whenever an Exception is raised transacted
  # queries will be rollbacked and false will be returned.
  # 
  # If no exception is raised transacted queries will be
  # commited to the database, and the result of the executed
  # function will be returned.
  function transaction($func, array $args=null)
  {
    if (is_string($func)) {
      $func = array($this, $func);
    }
    $this->db->transaction('begin');
    
    try
    {
      $rs = call_user_func_array($func, $args);
    }
    catch(Exception $e)
    {
      $this->db->transaction('rollback');
      return false;
    }
    
    $this->db->transaction('commit');
    return $rs;
  }
  
  # Creates or updates the record.
  function save()
  {
    $method = $this->new_record ? '_create' : '_update';
    return (bool)$this->$method();
  }
  
  # TODO: Test save_associated() with belongs_to, has_one, has_many & HABTM relationships.
  private function save_associated()
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
#          case 'has_many': break;
#          case 'has_and_belongs_to_many': break;
        }
        $rs &= $this->$assoc->save();
      }
    }
    return $rs;
  }
  
  # Generic create record method.
  # 
  # You better consider this method as private.
  # Do not use unless you know what you are doing (ie. you're hacking misago).
  # Use ActiveRecord_Base::create() instead.
  # 
  # @private
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
    $attributes = $this->attributes();
    $id = $this->db->insert($this->table_name, $attributes, $this->primary_key);
    if ($id)
    {
      $this->new_record = false;
      $this->id = $id;
      
      $this->save_associated();
      
      $this->after_create();
      $this->after_save();
      
      # dirty object:
      $this->__original_attributes = $this->__attributes;
      
      return $id;
    }
    return false;
  }
  
  # Generic update record method.
  # 
  # You better consider this method as private.
  # Do not use unless you know what you are doing (ie. you're hacking misago).
  # Use ActiveRecord_Base::update() or ActiveRecord_Base::update_attributes() instead.
  # 
  # @private
  protected function _update($attributes=null)
  {
    if ($attributes !== null) {
      $this->set_attributes($attributes);
    }
    
    if (!$this->is_valid()) {
      return false;
    }
    
    $this->before_save();
    $this->before_update();
    
    # nothing changed?
    if (!$this->changed) {
      return true;
    }
    
    # timestamps
    if (array_key_exists('updated_at', $this->columns) and empty($this->updated_at)) {
      $this->updated_at = new Time(null, 'datetime');
    }
    if (array_key_exists('updated_on', $this->columns) and empty($this->updated_on)) {
      $this->updated_on = new Time(null, 'date');
    }
    
    # update
    $conditions = array($this->primary_key => $this->id);
    $updates = $this->changes();
    if (empty($updates)) {
      return true;
    }
    $rs = $this->db->update($this->table_name, $updates, $conditions);
    
    if ($rs !== false)
    {
      $this->save_associated();
      
      $this->after_update();
      $this->after_save();
      
      # dirty object:
      $this->__original_attributes = $this->__attributes;
      
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
      $args = func_get_args();
      return $this->transaction('_block_create', &$args);
    }
    return false;
  }
  
  # Same as +create+ but throws an exception on failure.
  function do_create(array $attributes)
  {
    $args = func_get_args();
    $rs   = call_user_func_array(array($this, 'create'), $args);
    if ($rs === false) {
      throw new ActiveRecord_RecordNotSaved;
    }
    return $rs;
  }
  
  private function _block_create()
  {
    $args = func_get_args();
    $records = array();
    foreach($args as $attributes) {
      $records[] = $this->do_create($attributes);
    }
    return $records;
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
      $record->_update($attributes);
      
      return $record;
    }
    else {
      return $this->transaction('_block_update', array($id, $attributes));
    }
  }
  
  # Same as +update+ but throws an exception on failure.
  function do_update($id, $attributes)
  {
    $rs = $this->update($id, $attributes);
    if ($rs === false) {
      throw new ActiveRecord_RecordNotSaved;
    }
    return $rs;
  }
  
  private function _block_update($ids, $attributes)
  {
    $records = array();
    foreach($ids as $i => $id)
    {
      $records[] = $this->do_update($id, $attributes[$i]);
      $i++;
    }
    return $records;
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
  
  # Same as +delete+ but raises an ActiveRecord_Exception on error.
  function do_delete($id=null)
  {
    if (!$this->delete($id)) {
      throw new ActiveRecord_Exception();
    }
    return true;
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
  
  # Same as +destroy+ but raises an ActiveRecord_Exception on error.
  function do_destroy($id=null)
  {
    if (!$this->destroy($id)) {
      throw new ActiveRecord_Exception();
    }
    return true;
  }
  
  # Destroys many records at once.
  # 
  # Records aren't instanciated, and deletion callbacks aren't run.
  function destroy_all($conditions=null, $options=null)
  {
    return $this->db->delete($this->table_name, $conditions, $options);
  }
  
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
