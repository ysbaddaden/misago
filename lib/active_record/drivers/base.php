<?php

abstract class DBO_Base
{
  protected $field_quote = '"';
  protected $value_quote = "'";
  protected $escape_function;
  protected $conf;
  
  abstract function execute($sql);
  abstract function parse_results($results, $scope);
  
  function __construct(array $conf)
  {
    $this->conf =& $conf;
  }
  
  function query($sql, $scope)
  {
    $resultset = $this->execute();
    $this->parse_results($results, $scope);
  }
  
  # TODO: Throw an exception if data is empty.
  function insert($table, array $data)
  {
    $table  = $this->field($table);
    $fields = $this->fields(array_keys($data));
    $values = $this->values(array_values($data));
    return $this->execute("INSERT INTO $table ($fields) VALUES ($values) ;");
  }
  
  # TODO: Throw an exception if data is empty.
  function update($table, array $data, $conditions=null)
  {
    $updates = array();
    foreach($data as $f => $v)
    {
      $f = $this->field($f);
      $v = $this->value($v);
      $updates[] = "$f = $v";
    }

    $table   = $this->field($table);
    $updates = implode(', ', $updates);
    $where   = $this->conditions($conditions);
    return $this->execute("UPDATE $table SET $updates $where ;");
  }
  
  function delete($table, $conditions=null)
  {
    $table = $this->field($table);
    $where = $this->conditions($conditions);
    return $this->execute("DELETE FROM $table $where ;");
  }
  
  # Quotes a list of fields.
  # 
  # fields('a, b, c')            translates to "a", "b", "c"
  # fields(array('a', 'b', 'c')) translates to "a", "b", "c"
  # fields("COUNT(a)")           translates to COUNT("a")
  # fields("-! COUNT(a)")        translates to COUNT(a)
  # fields(array('a', '-! b'))   translates to "a", b
  # fields(' -! a, b'))          translates to a, "b"
  # 
  function fields($fields)
  {
    return $this->_map($fields, array($this, 'field'));
  }
  
  # Escapes a list of values.
  # 
  # values('a, b, c')            translates to 'a', 'b', 'c'
  # values(array('a', 'b', 'c')) translates to 'a', 'b', 'c'
  # values('-! a, b, c')         translates to a, b, c (ie. no parsing)
  # values(array('a', '-! b'))   translates to 'a', b
  # values(' -! a, b'))          translates to a, 'b'
  # 
  function values($values)
  {
    return $this->_map($values, array($this, 'value'));
  }
  
  protected function _map($arr, $callback)
  {
    if (empty($arr)) {
      return "";
    }
    if (!is_array($arr))
    {
      # do not process?
      if (strpos($arr, '-! ') === 0) {
        return str_replace('-! ', '', $arr);
      }
      
      # string to array
      $arr = explode(',', $arr);
    }
    return implode(', ', array_map($callback, $arr));
  }
  
  # Quotes a single field.
  # 
  # field('a')              translates to '"a"'
  # field("COUNT(a)")       translates to 'COUNT("a")'
  # field('-! a'))          translates to 'a' (ie. no quoting)
  # field("-! PASSWORD(a)") translates to 'PASSWORD(a)' (ie. no quoting)
  # field("DATE_ADD(a, b)") translates to 'DATE_ADD("a", "b")'
  # 
  # TODO: Do not escape special keywords, like DISTINCT.
  # 
  function field($field)
  {
    $field = trim($field);
    
    # do not process?
    if (strpos($field, '-! ') === 0) {
      return str_replace('-! ', '', $field);
    }
    
    # function?
    if (preg_match('/([\w_]+)\(([^\(\)]+)\)/', $field, $match)) {
      list(, $func, $field) = $match;
    }
    
    if (stripos($field, ','))
    {
      # multiple fields (ie. inside a function)
      $fields = explode(',', $field);
      foreach($fields as $i => $f) {
        $fields[$i] = $this->field(trim($f));
      }
      $field = implode(', ', $fields);
    }
    else
    {
      if (strpos($field, '.'))
      {
        # schema.table.field
        $field = explode('.', $field);
        foreach($field as $i => $k)
        {
          $field[$i] = ($k == '*') ? '*' :
            $this->field_quote.trim($k).$this->field_quote;
        }
        $field = implode('.', $field);
      }
      elseif ($field != '*') {
        $field = $this->field_quote.$field.$this->field_quote;
      }
    }
    return isset($func) ? "$func($field)" : $field;
  }
  
  # Escapes a single value.
  # Must be overriden to use driver's own escape function.
  # 
  # TODO: Do not escape functions (?)
  #
  function value($value)
  {
    $value = trim($value);
    if (strpos($value, '-! ') === 0) {
      return str_replace("-! ", "", $value);
    }
    return $this->value_quote.addslashes($value).$this->value_quote;
  }
  
  # Quotes and escapes conditions (field/value pairs).
  # 
  # All these calls are equivalent, and will generate the same output:
  #   "toto = 123 AND titi = 456"
  #   {"toto" => 123, "titi" => 456}
  #   ["toto = ? AND titi = ?", [123, 456]]
  #   ["toto = :toto AND titi = :titi", {"titi" => 123, "toto" => 456}]
  # 
  # Particular values:
  #   {'toto' => [1, 2, 3]}  # "toto IN (1, 2, 3)"
  # 
  # Particular operators:
  #   {'toto' => '> 123'}    # >, <, <>, <=, >=, LIKE
  #   {'toto' => 'BETWEEN 123 AND 456'}
  # 
  function conditions($conditions)
  {
    if (empty($conditions)) {
      return "";
    }
    if (is_string($conditions)) {
      return "WHERE $conditions";
    }
    
    # safe modes
    if (count($conditions) == 2 and !is_hash($conditions))
    {
      $values = $conditions[1];
      
      if (is_hash($values))
      {
        # uses symbols
        $symbols = array();
        foreach($values as $symbol => $value) {
          $symbols[":$symbol"] = $this->value($value);
        }
        $str = strtr($conditions[0], $symbols);
      }
      else
      {
        # uses question marks
        $token = strtok($conditions[0], '?');
        $str   = '';
        
        while($token !== false)
        {
          $str .= $token;
          if (!empty($values)) {
            $str .= $this->value(array_shift($values));
          }
          $token = strtok('?');
        }
      }
      return "WHERE $str";
    }
    
    $data = array();
    foreach($conditions as $f => $v)
    {
      $f  = $this->field($f);
      $op = '=';
      
      if (is_array($v))
      {
        foreach($v as $i => $vv) {
          $v[$i] = $this->value($vv);
        }
        $v     = implode(", ", $v);
        $str[] = "$f IN ($v)";
        continue;
      }
      elseif (preg_match('/^(<>|<|>|=|LIKE)\s+(.+)$/i', $v, $match)) {
        list(, $op, $v) = $match;
      }
      elseif (preg_match('/^BETWEEN\s+(.+)\s+AND\s+(.+)$/i', $v, $match))
      {
        $a  = $this->value($match[1]);
        $b  = $this->value($match[2]);
        $str[] = "$f BETWEEN $a AND $b";
        continue;
      }
      
      $v = $this->value($v);
      $str[] = "$f $op $v";
    }
    return "WHERE ".implode(" AND ", $str);
  }
}

?>
