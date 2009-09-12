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
      case 'avg':     $options['select'] = "avg($select)";   break;
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
    $results = $this->db->select_all($options);
    
    $data = array();
    foreach($results as $rs)
    {
      $rs = array_values($rs);
      $data[$rs[0]] = $this->cast_calculation_value($operation, $column, $rs[1]);
    }
    return $data;
  }
  
  private function execute_simple_calculation($operation, $column, &$options)
  {
    $sql = $this->build_sql_from_options($options);
    $value = $this->db->select_value($sql);
    return $this->cast_calculation_value($operation, $column, $value);
  }
  
  private function cast_calculation_value($operation, $column, $value)
  {
    switch($operation)
    {
      case 'count': return (int)$value;
      case 'avg':   return (float)$value;
      case 'sum':
        if ($column == '*') {
          return (int)$value;
        }
      case 'minimum':
      case 'maximum':
        switch($this->columns[$column]['type'])
        {
          case 'float':
          case 'numeric': return (float)$value;
          default: return (int)$value;
        }
    }
  }
  
}

?>
