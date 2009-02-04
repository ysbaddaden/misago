<?php

class ActiveRecord_ConnectionAdapters_Mysql extends ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  private   $link;
  protected $column_quote = '`';
  
  function is_active()
  {
    return (bool)$this->link;
  }
  
  function connect()
  {
    $callback = (isset($this->config['permanent']) and $this->config['permanent']) ?
      'mysql_pconnect' : 'mysql_connect';
    
    $this->link = $callback(
      $this->config['host'],
      $this->config['username'],
      $this->config['password'],
      true
    );
    
    if ($this->link === false) {
      throw new MisagoException("Unable to connect to MySQL database.", 500);
    }
  }
  
  function disconnect()
  {
    if ($this->link)
    {
      mysql_close($this->link);
      $this->link = null;
    }
  }
  
  function execute($sql)
  {
    return mysql_query($sql, $this->link);
  }
  
  function & select_rows($sql)
  {
    $results = $this->execute($sql);
    
    $data = array();
		if ($results and mysql_num_rows($results) > 0)
		{
      while ($row = mysql_fetch_row($results))
      {
        $result = array();
        foreach ($row as $idx => $value)
        {
          $table = mysql_field_table($results, $idx);
          $result[$table][mysql_field_name($results, $idx)] = $value;
        }
        array_push($data, $result);
      }
    }
    
    mysql_free_result($results);
    return $data;
  }
  
  function & columns($table)
  {
    $table   = $this->quote_table($table);
    $results = $this->execute("DESC $table ;");
    
    $columns = array();
		if ($results and mysql_num_rows($results) > 0)
		{
      while ($column = mysql_fetch_row($results))
      {
        $name = array_shift($column);
        $type = strtolower(array_shift($column));
        
        $columns[$name] = array(
          'type' => $type,
          'null' => (array_shift($column) != 'NO'),
          'key'  => (array_shift($column) == 'PRI') ? true  : false,
        );
        
        if ($columns == 'tinytext') {
          $columns[$name]['length'] = 255;
        }
        elseif (strpos($type, 'varchar(') === 0) {
          $columns[$name]['length'] = str_replace(array('varchar(', ')'), '', $type);
        }
      }
    }
    
    mysql_free_result($results);
    return $columns;
  }
  
  function escape_value($value)
  {
    return mysql_real_escape_string($value, $this->link);
  }
  
  
  function create_database($database, array $options=null)
  {
    $database = $this->quote_table($database);
    $sql = "CREATE DATABASE $database";
    
    if (isset($options['charset']))
    {
      $charset = $this->quote_value($options['charset']);
      $sql .= " DEFAULT CHARSET = $charset";
    }
    return $this->execute("$sql ;");
  }
  
  function drop_database($database)
  {
    $database = $this->quote_table($database);
    return $this->execute("DROP DATABASE $database ;");
  }
  
  function select_database($database)
  {
    return mysql_select_db($database, $this->link);
  }
  
  # 
  # definition:
  #   :column :type, :null, :default, :limit
  #   :timestamps (adds timestamp columns)
  #
  # options:
  #   :id => bool (adds an 'id' column)
  #   :primary_key (name of PK)
  #   :temporary => bool (temporary table)
  #   :force => bool (drop before create)
  #   :options => string ("engine=innodb")
  # 
  function create_table($table, array $definition, array $options=null)
  {
    
  }
  
  function rename_table($from, $to)
  {
    
  }
  
  function drop_table($table)
  {
    $table = $this->quote_table($table);
    return $this->execute("DROP TABLE $table ;");
  }
  
  
  function add_column()
  {
    
  }
  
  function change_column()
  {
    
  }
  
  function rename_column()
  {
    
  }
  
  function remove_column()
  {
    
  }
  
  function add_timestamps()
  {
    
  }
  
  function remove_timestamps()
  {
    
  }
  
  
  function create_index()
  {
    
  }
  
  function remove_index()
  {
    
  }
}

?>
