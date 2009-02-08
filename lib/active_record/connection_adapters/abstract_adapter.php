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
  #   :primary_key => string (name of PK)
  #   :temporary   => bool   (temporary table?)
  #   :force       => bool   (drop before create?)
  #   :options     => string ("engine=innodb")
  #
  function new_table($table, array $options=null)
  {
    return new ActiveRecord_Table($table, $options, $this);
  }
  
  # 
  # definition:
  #   :columns => { :name => { :type, :limit, :null, :default, :signed } }
  #   :primary_key => string (name of PK)
  #   :temporary   => bool   (temporary table?)
  #   :force       => bool   (drop before create?)
  #   :options     => string ("engine=innodb")
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
    $sql     = "CREATE TABLE $table ( $columns )";
    
    if (isset($definition['options'])) {
      $sql .= ' '.$definition['options'];
    }
    return $this->execute("$sql ;");
  }
  
  function drop_table($table)
  {
    $table = $this->quote_table($table);
    return $this->execute("DROP TABLE $table ;");
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
}

?>
