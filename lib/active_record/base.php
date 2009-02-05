<?php

#
# Magic attributes: created_at, created_on, updated_at, updated_on
#
class ActiveRecord_Base extends ActiveRecord_Record
{
  protected $class_name;

  protected $db;
  protected $table_name;
  protected $table_columns;

  public    $errors;

  # one-to-many relationships
  protected $belongs_to = array();
  protected $has_one    = array();
  protected $has_many   = array();

  # many-to-many relationships (throught an intermediate table)
  protected $has_and_belongs_to_many = array();


  # Todo: new Record(123)
  function __construct($arg=null)
  {
    $this->class_name = get_class($this);
    $this->table_name = String::pluralize(String::underscore($this->class_name));

    $this->db = new ActiveRecord_Connection::create($_ENV['environment']);

    if ($arg !== null)
    {
      if (!is_array($arg)) {
        $arg = $this->find($arg);
      }
      parent::__construct($arg);
    }
  }

  # Returns the list of columns for the table associated with this class.
  function & columns()
  {
    return $this->_table_columns;;
  }

  function & column_names()
  {
    $column_names = array_keys($this->table_columns);
    return $column_names;
  }

  # Returns a string like ‘Post id:integer, title:string, body:text‘
  function inspect()
  {

  }

  # Checks wether an entry exists.
  function exists($id_or_conditions)
  {

  }

  # Looks for records.
  #
  # Find many:
  #   find()
  #   find(':all')
  #   find(':all', array('conditions' => 'created_at >= 2008-10-12'))
  #   find(array('limit' => 10, 'page' => 2))
  #
  # Find one:
  #   find(':first')
  #   find(':first', array('conditions' => array('id' => 123)))
  #
  # Find a specific row:
  #   find(123)
  #
  # Available options:
  #   -fields- select
  #   conditions
  #   limit
  #   offset (or page)
  #   order
  #   group
  #   joins
  #   from
  #
  function & find()
  {
    $args = func_get_args();
    switch(func_num_args())
    {
      case 2:
        $scope   = $args[0];
        $options = $args[1];
      break;

      case 1:
        if (is_string($args[0]))
        {
          $scope   = $args[0];
          $options = array();
        }
        else
        {
          $scope   = ':all';
          $options = $args[0];
        }
      break;

      default:
        $scope   = ':all';
        $options = array();
    }

    $results = $this->db->select($this->_table, &$options);

    if (!empty($results))
    {
      foreach($results as $i => $result)
      {
#       if (PHP_VERSION < '5.3.0')
#       {
          $class = $this->class_name;
          $results[$i] = new $class($data);
#       }
#       else {
#         $results[$i] = new static($data);
#       }
      }
      return ($scope == ':first') ? $results[0] : $results;
    }
    return ($scope == ':first') ? null : array();
  }

  function find_by_sql($sql)
  {

  }

  # Shortcut for find(':all').
  function all()
  {
    $args = func_get_args();
    array_unshift($args, ':all');
    return call_user_func_array(array($this, 'find'), $args);
  }

  # Shortcut for find(':first').
  function first()
  {
    $args = func_get_args();
    array_unshift($args, ':first');
    return call_user_func_array(array($this, 'find'), $args);
  }


  # Counts rows in a table.
  #
  # count()
  # count($conditions)
  # count($conditions, $join)
  # count(array $conditions)
  # count(hash $options)
  # count($column, hash $options)
  #
  # available options:
  #   conditions
  #   joins
  #   limit
  #   order
  #   having
  #   select
  #   distinct (for count only)
  #
  function count()
  {

  }

  function count_by_sql($sql)
  {

  }

  function average($field, $options=null)
  {

  }

  function maximum($field, $options=null)
  {

  }

  function minimum($field, $options=null)
  {

  }

  function sum($field, $options=null)
  {

  }


  # returns new Object or null
  function save()
  {

  }

  # Raises an exception on error.
  function do_save()
  {
    if ($this->save($attributes) === null) {
      throw new ActiveRecord_Exception(ActiveRecord_Exception_SaveFailed);
    }
  }


  # create({:name => 'toto'})
  # create({:name => 'toto'}, {:name => 'titi'})
  #
  # returns new Object or null
  function create($attributes=null)
  {

  }

  # Raises an exception on error.
  function do_create($attributes=null)
  {
    if ($this->create($attributes) === null) {
      throw new ActiveRecord_Exception(ActiveRecord_Exception_CreateFailed);
    }
  }


  # update(1, {:name => "toto"})
  # update([1, 2], [{:name => "toto"}, {:name => "titi"}])
  #
  # returns new Object or null
  function update($id, $attributes)
  {

  }

  function update_all($updates, $conditions=null, $options=null)
  {

  }

  function update_attribute($attribute, $value)
  {

  }

  function update_attributes(array $updates)
  {

  }


  function delete($id)
  {

  }

  function delete_all($conditions=null, $options=null)
  {

  }

  # Instanciates object before destroying it. Less efficient than
  # delete, but permits to operate some cleanup when deleting.
  function destroy($id)
  {

  }

  # Instanciates all objects before destroying it. Less efficient than
  # delete, but permits to operate some cleanup when deleting.
  function destroy_all($conditions=null)
  {

  }

  # Transforms an attribute key name to a more human readable format.
  function human_attribute_name($attribute)
  {

  }

  # Transforms class name to a more human readable format.
  function human_name()
  {

  }

  function & __sleep()
  {
    return $this->column_names();
  }

  function __wakeup()
  {

  }

  static function __set_state($attributes)
  {
#   if (PHP_VERSION < '5.3.0')
#   {
      $class = $this->class_name;
      return new $class($attributes);
#   }
#   else {
#     return new static($data);
#   }
  }

  function __toString()
  {

  }
  
  function to_xml()
  {

  }

  function to_json()
  {

  }
}

?>
