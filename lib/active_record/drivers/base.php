<?php

# NOTE: DEPRECATED in favor of ActiveRecord_ConnectionAdapters_AbstractAdapter
abstract class DBO_Base
{
  protected $field_quote = '"';
  protected $value_quote = "'";
  protected $config;
  
  abstract function execute($sql);
  abstract function parse_results($results, $scope);
  
  function __construct(array $config)
  {
    $this->config =& $config;
  }
  
  function query($sql, $scope=null)
  {
    $results = $this->execute($sql);
    return $this->parse_results($results, $scope);
  }
  
  function select($table, $options=null)
  {
    $fields     = '*';
    $conditions = null;
    $limit      = null;
    $page       = null;
    $group      = null;
    $order      = null;
    
    if (is_string($options)) {
      $fields = $options;
    }
    elseif (is_array($options))
    {
      foreach($options as $k => $v) {
        $$k = $v;
      }
    }
    
    $table  = $this->field($table);
    $fields = $this->fields($fields);
    $where  = $this->conditions($conditions);
    $limit  = $this->limit($limit, $page);
    $group  = $this->group($group);
    $order  = $this->order($order);
    
    return $this->query("SELECT $fields FROM $table $where $group $order $limit ;");
  }
  
  function insert($table, array $data)
  {
    $table  = $this->field($table);
    $fields = $this->fields(array_keys($data));
    $values = $this->values(array_values($data));
    
    return $this->execute("INSERT INTO $table ($fields) VALUES ($values) ;");
  }
  
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
  
  # 
  function delete($table, $conditions=null)
  {
    $table = $this->field($table);
    $where = $this->conditions($conditions);
    
    return $this->execute("DELETE FROM $table $where ;");
  }
  
  # Sanitizes a list of fields.
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
  
  # Sanitizes a list of values.
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
  
  # Sanitizes a single field.
  # 
  # field('a')              translates to '"a"'
  # field("COUNT(a)")       translates to 'COUNT("a")'
  # field('-! a'))          translates to 'a' (ie. no quoting)
  # field("-! PASSWORD(a)") translates to 'PASSWORD(a)' (ie. no quoting)
  # field("DATE_ADD(a, b)") translates to 'DATE_ADD("a", "b")'
  # 
  # IMPROVE: Do not escape special keywords, like DISTINCT.
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
  
  # Sanitizes a single value.
  # Must be overriden to use driver's own escape function.
  # 
  function value($value, $quote=true)
  {
    $value = trim($value);
    if (strpos($value, '-! ') === 0) {
      return str_replace("-! ", "", $value);
    }
#    return $this->value_quote.addslashes($value).$this->value_quote;
    $value = addslashes($value);
    return $quote ? $this->value_quote.$value.$this->value_quote : $value;
  }
  
  # Sanitizes conditions (field/value pairs).
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
    
    # uses symbols
    if (count($conditions) == 2 and !is_hash($conditions)) {
      return $this->conditions_with_symbols($conditions[0], $conditions[1]);
    }
    
    # uses field/value pairs
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
  
  function conditions_with_symbols($sql, $values)
  {
    if (is_hash($values))
    {
      # uses symbols
      $symbols = array();
      foreach($values as $symbol => $value) {
        $symbols[":$symbol"] = $this->value($value);
      }
      $str = strtr($sql, $symbols);
    }
    else
    {
      # uses question marks
      $token = strtok($sql, '?');
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
  
  function order($fields, $type='ORDER')
  {
    if ($fields)
    {
      $fields = explode(',', $fields);
      foreach($fields as $i => $field)
      {
        $parts = explode(' ', trim($field), 2);
        $fields[$i]  = $this->field($parts[0]);
        $fields[$i] .= isset($parts[1]) ? ' '.$parts[1] : '';
      }
      $fields = implode(', ', $fields);
      return "$type BY $fields";
    }
    return '';
  }
  
  function group($fields)
  {
    return $this->order($fields, 'GROUP');
  }
  
  function limit($limit, $page=null)
  {
    if ($limit)
    {
      $offset = ($page > 1) ? " OFFSET ".(($page - 1) * $limit) : '';
      return ($page > 1) ? "LIMIT $limit$offset" : "LIMIT $limit";
    }
    return '';
  }
  
  
  # Accepts an array of conditions. The array has each value
  # sanitized and interpolated into the SQL statement.
  # 
  # ["name = :name", {name => 'toto'}]
  # ["name = '%s' AND group_id = %d", 'toto', 123]
  # 
  function sanitize_sql_array($ary)
  {
    if (!isset($ary[1])) {
      return isset($ary[0]) ? $ary[0] : '';
    }
    if (is_hash($ary[1]))
    {
      # uses symbols
      $symbols = array();
      foreach($ary[1] as $symbol => $value) {
        $symbols[":$symbol"] = $this->value($value);
      }
      $sql = strtr($ary[0], $symbols);
    }
    else
    {
      for($i=1, $len = count($ary); $i<$len; $i++) {
          $ary[$i] = $this->value($ary[$i], false);
      }
      $sql = call_user_func_array('sprintf', $ary);
    }
    return $sql;
  }
  
  # Accepts an array, hash, or string of SQL conditions and
  # sanitizes them into a valid SQL fragment for a SET clause.
  function sanitize_sql_for_assignment($assignments)
  {
    if (is_hash($assignments)) {
      return $this->sanitize_sql_hash_for_assignment($assignments);
    }
    elseif (is_array($assignments)) {
      return $this->sanitize_sql_array($assignments);
    }
    return $assignments;
  }
  
  # Accepts an array, hash, or string of SQL conditions and
  # sanitizes them into a valid SQL fragment for a WHERE clause.
  function sanitize_sql_for_conditions($conditions)
  {
    if (is_hash($conditions)) {
      return $this->sanitize_sql_hash_for_conditions(&$conditions);
    }
    elseif (is_array($conditions)) {
      return $this->sanitize_sql_array(&$conditions);
    }
    return $conditions;
  }
  
  # Sanitizes a hash of attribute/value pairs into SQL conditions
  # for a WHERE clause.
  function sanitize_sql_hash_for_conditions(array $conditions)
  {
    $conditions = $this->sanitize_sql_hash(&$conditions);
    return implode(' AND ', $conditions);
  }

  # Sanitizes a hash of attribute/value pairs into SQL conditions
  # for a SET clause.
  function sanitize_sql_hash_for_assignment(array $assignments)
  {
    $assignments = $this->sanitize_sql_hash(&$assignments);
    return implode(', ', $assignments);
  }
  
  # Sanitizes a hash of attribute/value pairs into SQL conditions
  #
  # ["a" => 'b', "c" => 'd']
  function & sanitize_sql_hash(array $hash)
  {
    $sanitized = array();
    foreach($hash as $f => $v)
    {
      $f = $this->field($f);
      $v = $this->value($v);
      $sanitized[] = "$f = $v";
    }
    return $sanitized;
  }
}

?>
