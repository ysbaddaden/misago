<?php
namespace Misago\ActiveRecord;

# Computes data.
class Calculations extends Validations
{
  # Generic calculation method.
  # 
  # Use whatever mathematic SQL function. For <tt>count</tt>, <tt>average</tt>,
  # <tt>sum</tt>, <tt>minimum</tt> and <tt>maximum</tt>, use the optimized
  # methods instead.
  # 
  # It returns a single float/integer if no +group+ option is present.
  # otherwise returns a column => value pair hash.
  # 
  # Options:
  # 
  # - +select+
  # - +distinct+
  # 
  static function calculate($operation, $column='*', $options=array())
  {
    if (isset($options['select'])) {
      $column = $options['select'];
    }
    $select = (isset($options['distinct']) and $options['distinct']) ?
      "distinct($column)" : $column;
    
    switch($operation)
    {
      case 'count':   $options['select'] = "count($select)"; break;
      case 'minimum': $options['select'] = "min($select)";   break;
      case 'maximum': $options['select'] = "max($select)";   break;
      case 'sum':     $options['select'] = "sum($select)";   break;
      case 'average': $options['select'] = "avg($select)";   break;
    }
    
    if (isset($options['group'])) {
      return static::execute_grouped_calculation($operation, $column, $options);
    }
    return static::execute_simple_calculation($operation, $column, $options);
  }
  
  static function average($column, $options=array())
  {
    return static::calculate('average', $column, $options);
  }
  
  # Counts rows.
  # 
  #   People::count()
  #   People::count('date')
  #   People::count(array('conditions' => 'age = 26'))
  #   People::count('id', array('conditions' => 'age = 26'))
  # 
  # Returned value is always is an integer.
  static function count()
  {
    $column  = '*';
    $options = array();
    $args    = func_get_args();
    switch(func_num_args())
    {
      case 1: is_array($args[0]) ? $options = $args[0] : $column = $args[0]; break;
      case 2: list($column, $options) = $args; break;
    }
    return static::calculate('count', $column, $options);
  }
  
  static function sum($column, $options=array())
  {
    return static::calculate('sum', $column, $options);
  }
  
  static function minimum($column, $options=array())
  {
    return static::calculate('minimum', $column, $options);
  }
  
  static function maximum($column, $options=array())
  {
    return static::calculate('maximum', $column, $options);
  }
  
  private static function execute_grouped_calculation($operation, $column, &$options)
  {
    $instance = static::instance();
    
    $options['select'] = "{$options['group']}, {$options['select']}";
    $sql     = $instance->build_sql_from_options($options);
    $results = $instance->connection->select_all($sql);
    
    $values = array();
    foreach($results as $rs)
    {
      $rs = array_values($rs);
      $values[$rs[0]] = ($operation == 'count') ? (int)$rs[1] : $rs[1];
    }
    return $values;
  }
  
  private static function execute_simple_calculation($operation, $column, &$options)
  {
    $sql = static::instance()->build_sql_from_options($options);
    $rs  = static::$connection->select_value($sql);
    return ($operation == 'count') ? (int)$rs : $rs;
  }
}

?>
