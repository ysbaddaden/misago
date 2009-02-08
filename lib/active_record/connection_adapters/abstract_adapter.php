<?php

abstract class ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  public  $COLUMN_QUOTE = '"';
  public  $VALUE_QUOTE  = "'";
  public  $NATIVE_DATABASE_TYPES = array();
  
  protected $config;
  
  abstract function connect();
  abstract function disconnect();
  
  abstract function execute($sql);
#  abstract function & select_rows($sql);
  abstract function & select_all($sql);
#  abstract function & select_one($sql);
#  abstract function select_value($sql);
  abstract function & select_values($sql);

  abstract function & columns($sql);
  abstract function is_active();
  abstract function escape_value($value);
  
  function __construct(array $config)
  {
    $this->config = $config;
    $this->connect();
  }
  
  function config($name)
  {
    return $this->config[$name];
  }
  
  function quote_table($table)
  {
    return $this->quote_column($table);
  }
  
  function quote_column($column)
  {
    if (strpos($column, '.'))
    {
      $segments = explode('.', $column);
      foreach($segments as $i => $column) {
        $segments[$i] = $this->COLUMN_QUOTE.$column.$this->COLUMN_QUOTE;
      }
      return implode('.', $segments);
    }
    return $this->COLUMN_QUOTE.$column.$this->COLUMN_QUOTE;
  }
  
  function quote_value($value)
  {
    return $this->VALUE_QUOTE.$this->escape_value($value).$this->VALUE_QUOTE;
  }
  
  #
  # options:
  #   :temporary   => bool   (temporary table?)
  #   :id          => bool   (automatically add 'id' primary key?)
  #   :force       => bool   (drop before create?)
  #   :options     => string ("engine=innodb")
  #
  function new_table($table, array $options=null)
  {
    return new ActiveRecord_Table($table, $options, $this);
  }
  
  # 
  # definition:
  #   :columns   => { :name => { :type, :limit, :null, :default, :signed } }
  #   :temporary => bool   (temporary table?)
  #   :force     => bool   (drop before create?)
  #   :options   => string ("engine=innodb")
  # 
  # TODO: Add support for :force (true: drop table before create, false: create if not exists).
  # 
  function create_table($table, array $definition)
  {
    if (empty($definition['columns'])) {
      throw new MisagoException("Can't create table: there are no columns.", 500);
    }
    
    $columns = array();
    foreach($definition['columns'] as $name => $column)
    {
      $type = strtolower($column['type']);
      
      if (isset($this->NATIVE_DATABASE_TYPES[$type])
        and is_array($this->NATIVE_DATABASE_TYPES[$type]))
      {
        $column = array_merge($this->NATIVE_DATABASE_TYPES[$type], $column);
        
        $def = $column['name'];
        
        if (isset($column['limit'])) {
          $def .= "({$column['limit']})";
        }
        if (isset($column['signed']) and !$column['signed']) {
          $def .= " UNSIGNED";
        }
        if (isset($column['null']) and !$column['null']) {
          $def .= " NOT NULL";
        }
        if (isset($column['default'])) {
          $def .= " DEFAULT {$column['default']}";
        }
      }
      else {
        $def = $this->NATIVE_DATABASE_TYPES[$column['type']];
      }
      
      $name = $this->quote_table($name);
      $columns[] = "$name $def";
    }
    
    $table   = $this->quote_table($table);
    $columns = implode(', ', $columns);
    
    $sql = (isset($definition['temporary']) and $definition['temporary']) ?
      "CREATE TEMPORARY TABLE $table ( $columns )" :
      "CREATE TABLE $table ( $columns )";
    
    if (isset($definition['options'])) {
      $sql .= ' '.$definition['options'];
    }
    return $this->execute("$sql ;");
  }
  
  function drop_table($table, $temporary=false)
  {
    $table = $this->quote_table($table);
    return $this->execute("DROP ".($temporary ? "TEMPORARY" : '')." TABLE $table ;");
  }
  
  
  # Returns a single hash of columns => values.
  function & select_one($sql)
  {
    $rs = $this->select_all($sql);
    $rs = isset($rs[0]) ? $rs[0] : false;
    return $rs;
  }
  
  # Returns a single value.
  function select_value($sql)
  {
    $rs = $this->select_one($sql);
    return (count($rs) > 0) ? array_shift($rs) : null;
  }
  
  # Inserts a row in a table.
  function insert($table, array $data, $returning=null)
  {
    $table  = $this->quote_table($table);
    $fields = array();
    $values = array();
    
    foreach($data as $field => $value)
    {
      $fields[] = $this->quote_column($field);
      $values[] = $this->quote_value($value);
    }
    $fields = implode(', ', $fields);
    $values = implode(', ', $values);
    
    return $this->execute("INSERT INTO $table ( $fields ) VALUES ( $values ) ;");
  }
  
  # Updates rows in a table.
  function update($table, array $data, array $conditions=null)
  {
    $table       = $this->quote_table($table);
    $assignments = $this->sanitize_sql_for_assignment($data);
    $where = empty($conditions) ? '' :
      'WHERE '.$this->sanitize_sql_for_conditions($conditions);
    return $this->execute("UPDATE $table SET $assignments $where ;");
  }
  
  
  # Accepts an array, hash, or string of SQL assignments and
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
        $symbols[":$symbol"] = $this->quote_value($value);
      }
      $sql = strtr($ary[0], $symbols);
    }
    else
    {
      for($i=1, $len = count($ary); $i<$len; $i++) {
          $ary[$i] = $this->escape_value($ary[$i], false);
      }
      $sql = call_user_func_array('sprintf', $ary);
    }
    return $sql;
  }
  
  # Sanitizes a hash of attribute/value pairs for use in
  # SQL conditions or assignments.
  #
  # ["a" => 'b', "c" => 'd']
  function & sanitize_sql_hash(array $hash)
  {
    $sanitized = array();
    foreach($hash as $f => $v)
    {
      $f = $this->quote_column($f);
      $v = $this->quote_value($v);
      $sanitized[] = "$f = $v";
    }
    return $sanitized;
  }
  
  # Sanitizes a hash of attribute/value pairs into SQL conditions
  # for a SET clause.
  function sanitize_sql_hash_for_assignment(array $assignments)
  {
    $assignments = $this->sanitize_sql_hash(&$assignments);
    return implode(', ', $assignments);
  }
  
  # Sanitizes a hash of attribute/value pairs into SQL conditions
  # for a WHERE clause.
  function sanitize_sql_hash_for_conditions(array $conditions)
  {
    $conditions = $this->sanitize_sql_hash(&$conditions);
    return implode(' AND ', $conditions);
  }
}

?>
