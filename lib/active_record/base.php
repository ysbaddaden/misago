<?php

class ActiveRecord_Base extends ActiveRecord_Record
{
  public $_name;
  public $_table;
  public $_table_columns;
  
  # Todo: new Record(123)
  function __construct($arg=null)
  {
    $this->_name = get_name($this);
    $this->_table = String::pluralize(String::underscore($this->_name));
    
    $this->db = new DBO($_ENV['environment']);
    
    if ($arg !== null)
    {
      if (!is_array($arg))
      {
#       $arg = $this->find_by_id(':first', $arg);
#       $arg = $this->find(':first', array(':conditions' => array('id' => $id)));
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
    $column_names = array_keys($this->_table_columns);
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
  # Available options: fields, conditions, limit, page, order, group
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
          $class = $this->_name;
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
  
  /*
  # Shortcut for find(':last').
  function last()
  {
    $args = func_get_args();
    array_unshift($args, ':last');
    return call_user_func_array(array($this, 'find'), $args);
  }
  */
  
  function count()
  {
    
  }
  
  function count_by_sql($sql)
  {
    
  }
  
  
  # create({:name => 'toto'})
  # create({:name => 'toto'}, {:name => 'titi'})
  function create($attributes=null)
  {
    
  }
  
  # update(1, {:name => "toto"})
  # update([1, 2], [{:name => "toto"}, {:name => "titi"}])
  function update($id, $attributes)
  {
    
  }
  
  function update_all($updates, $conditions=null, $options=null)
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
      $class = $this->_name;
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
