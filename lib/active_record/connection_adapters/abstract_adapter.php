<?php

abstract class ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  protected $config;
  protected $column_quote = '"';
  protected $value_quote  = "'";
  
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
        $segments[$i] = $this->column_quote.$column.$this->column_quote;
      }
      return implode('.', $segments);
    }
    return $this->column_quote.$column.$this->column_quote;
  }
  
  function quote_value($value)
  {
    return $this->value_quote.$this->escape_value($value).$this->value_quote;
  }
}

?>
