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
  abstract function & select_rows($sql);
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
  #   :temporary   => bool   (temporary table)
  #   :force       => bool   (drop before create)
  #   :options     => string ("engine=innodb")
  #
  function new_table($table, array $options=null)
  {
    return new ActiveRecord_Table($table, $options, $this);
  }
  
  # 
  # definition:
  #   :column :type, :null, :default, :limit
  #
  function create_table($table, array $definition)
  {
    $columns = $definition['columns'];
    
  }
}

?>
