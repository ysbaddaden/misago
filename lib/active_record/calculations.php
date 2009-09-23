<?php

class ActiveRecord_Calculations extends ActiveRecord_Behaviors
{
  function calculate($operation, $column='*', $options=array())
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
      return $this->execute_grouped_calculation($operation, $column, $options);
    }
    return $this->execute_simple_calculation($operation, $column, $options);
  }
  
  function average($column, $options=array())
  {
    return $this->calculate('average', $column, $options);
  }
  
  # Counts rows.
  # 
  #   $people->count()
  #   $people->count('date')
  #   $people->count(array('conditions' => 'age = 26'))
  #   $people->count('id', array('conditions' => 'age = 26'))
  # 
  function count()
  {
    $column  = '*';
    $options = array();
    $args    = func_get_args();
    switch(func_num_args())
    {
      case 1: is_array($args[0]) ? $options = $args[0] : $column = $args[0]; break;
      case 2: list($column, $options) = $args; break;
    }
    return $this->calculate('count', $column, $options);
  }
  
  function sum($column, $options=array())
  {
    return $this->calculate('sum', $column, $options);
  }
  
  function minimum($column, $options=array())
  {
    return $this->calculate('minimum', $column, $options);
  }
  
  function maximum($column, $options=array())
  {
    return $this->calculate('maximum', $column, $options);
  }
  
  
  private function execute_grouped_calculation($operation, $column, &$options)
  {
    $options['select'] = "{$options['group']}, {$options['select']}";
    $sql     = $this->build_sql_from_options($options);
    $results = $this->db->select_all($sql);
    
    $values = array();
    foreach($results as $rs)
    {
      $rs = array_values($rs);
      $values[$rs[0]] = ($operation == 'count') ? (int)$rs[1] : $rs[1];
    }
    return $values;
  }
  
  private function execute_simple_calculation($operation, $column, &$options)
  {
    $sql = $this->build_sql_from_options($options);
    $rs  = $this->db->select_value($sql);
    return ($operation == 'count') ? (int)$rs : $rs;
  }
}

?>
