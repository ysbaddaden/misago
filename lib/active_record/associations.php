<?php
/**
 * 
 * @package ActiveRecord
 * 
 * TODO: Implement :throught associations.
 * TODO: Implement has_and_belongs_to_many association.
 */
abstract class ActiveRecord_Associations extends ActiveRecord_Record
{
  protected $associations = array(
    'belongs_to' => array(),
    'has_one'    => array(),
    'has_many'   => array(),
  );
  protected $belongs_to   = array();
  protected $has_one      = array();
  protected $has_many     = array();
  
  function __construct($arg=null)
  {
    # relationships
    $this->configure_associations('belongs_to');
    $this->configure_associations('has_one');
    $this->configure_associations('has_many');
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
          case 'belongs_to':
            $def['foreign_key'] = String::underscore($name).'_'.$def['primary_key'];
          break;
          
          case 'has_one':
            $def['foreign_key'] = String::underscore(get_class($this)).'_'.$this->primary_key;
          break;
          
          case 'has_many':
            $def['foreign_key'] = String::underscore(get_class($this)).'_'.$this->primary_key;
          break;
        }
      }
    }
  }
  
  function __get($attribute)
  {
    # association: belongs to
    if (array_key_exists($attribute, $this->belongs_to))
    {
      $conditions = array($this->belongs_to[$attribute]['primary_key'] => $this->{$this->belongs_to[$attribute]['foreign_key']});
      $class      = String::camelize($attribute);
      $record     = new $class();
      return $this->$attribute = $record->find(':first', array('conditions' => &$conditions));
    }
    
    # association: has one
    elseif (array_key_exists($attribute, $this->has_one))
    {
      $conditions = array($this->has_one[$attribute]['foreign_key'] => $this->{$this->primary_key});
      $class      = String::camelize($attribute);
      $record     = new $class();
      return $this->$attribute = $record->find(':first', array('conditions' => &$conditions));
    }
    
    # association: has many
    elseif (array_key_exists($attribute, $this->has_many))
    {
      $conditions = array($this->has_many[$attribute]['foreign_key'] => $this->{$this->primary_key});
      $class      = String::camelize(String::singularize($attribute));
      $record     = new $class();
      return $this->$attribute = $record->find(':all', array('conditions' => &$conditions));
    }
    
    # another
    return parent::__get($attribute);
  }
}

?>
