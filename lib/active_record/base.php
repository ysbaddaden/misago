<?php

class ActiveRecord_Base extends ActiveRecord_Record
{
  public $_class;
  public $_table;
  
  function __construct($arg=null)
  {
    $this->_class = get_class($this);
    $this->_table = String::pluralize(String::underscore($this->_class));
    
    $this->db = new DBO($_ENV['environment']);
    
    if ($arg !== null)
    {
      if (!is_array($arg))
      {
#        $arg = $this->find_by_id($arg);
        $arg = $this->find(':first', array(':conditions' => array('id' => $id)));
      }
      parent::__construct($arg);
    }
  }
  
  # Look for records.
  # 
  # Find many:
  #   find()
  #   find(':all')
  #   find(':all', array('conditions' => 'id = 123'))
  #   find(array('limit' => 10, 'page' => 2))
  # 
  # Find one:
  #   find(':first')
  #   find(':first', array('conditions' => array('id' => 234)))
  # 
  # Available options: fields, conditions, limit, page, order
  #
  # TODO: ActiveRecord_Base::find()
  function find()
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
    
    $data = $this->db->select($this->_table, &$options);
    
    $class = $this->_class;
    return new $class($data);
    # return new static($data);
  }
}

?>
