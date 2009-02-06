<?php

class ActiveRecord_ConnectionAdapters_MysqlAdapter extends ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  public  $COLUMN_QUOTE = '`';
  public  $NATIVE_DATABASE_TYPES = array(
    'primary_key' => "INTEGER AUTO_INCREMENT PRIMARY KEY",
    'string'      => array('name' => 'VARCHAR', 'limit' => 255),
    'text'        => array('name' => 'TEXT'),
    'integer'     => array('name' => 'INT', 'limit' => 4),
    'date'        => array('name' => 'DATE'),
    'time'        => array('name' => 'TIME'),
    'datetime'    => array('name' => 'DATETIME'),
    'bool'        => array('name' => 'BOOLEAN'),
    'binary'      => array('name' => 'BLOB'),
  );
  private $link;
  
  function escape_value($value)
  {
    return mysql_real_escape_string($value, $this->link);
  }
  
  
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
  
  # TODO: Test select_rows()
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
        $data[] = $result;
      }
    }
    
    mysql_free_result($results);
    return $data;
  }
  
  # TODO: Test columns()
  function & columns($table)
  {
    $table   = $this->quote_table($table);
    $results = $this->execute("DESC $table ;");
    
    
    $columns = array();
		if ($results and mysql_num_rows($results) > 0)
		{
      while ($row = mysql_fetch_row($results))
      {
        list($name, $type, $is_null, $key, $default,) = $row;
        $column = array();
        $type   = strtoupper($type);
        
        if (preg_match('/(\w+)\(([^\)]+)\)/', $type, $match))
        {
          $column['type']  = $match[1];
          $column['limit'] = (int)$match[2];
        }
        elseif ($type == 'TINYTEXT')
        {
          $column['type']  = 'string';
          $column['limit'] = 255;
        }
        else {
          $column['type'] = $type;
        }
        
        foreach($this->NATIVE_DATABASE_TYPES as $type => $def)
        {
          if ($def['name'] == $column['type']) {
            $column['type'] = $type;
          }
        }
        
        $column['null'] = ($is_null == 'NO') ? false : true;
        $columns[$name] = $column;
      }
    }
    
    mysql_free_result($results);
    return $columns;
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
  
  # TODO: Test select_database()
  function select_database($database)
  {
    return mysql_select_db($database, $this->link);
  }
  
  
  function rename_table($from, $to)
  {
    
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
  
  
  function create_index()
  {
    
  }
  
  function remove_index()
  {
    
  }
}

?>
