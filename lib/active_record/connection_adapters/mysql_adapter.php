<?php
/**
 * Mysql adapter.
 * 
 * @package ActiveRecord
 * @subpackage ConnectionAdapters
 */
class ActiveRecord_ConnectionAdapters_MysqlAdapter extends ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  public  $COLUMN_QUOTE = '`';
  public  $NATIVE_DATABASE_TYPES = array(
    'primary_key' => "INTEGER AUTO_INCREMENT PRIMARY KEY",
    'string'      => array('name' => 'VARCHAR', 'limit' => 255),
    'text'        => array('name' => 'TEXT'),
    'integer'     => array('name' => 'INT',   'limit' => 4),
    'double'      => array('name' => 'FLOAT', 'limit' => 4),
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
      throw new ActiveRecord_ConnectionNotEstablished("Unable to connect to MySQL database.");
    }
    
#    if (!empty($this->config['database'])) {
#      $this->select_database($this->config['database']);
#    }
  }
  
  function disconnect()
  {
    if ($this->link)
    {
      mysql_close($this->link);
      $this->link = null;
    }
  }
  
  # IMPROVE: Add some error logging (in 'production' environment).
  function execute($sql)
  {
#    echo "$sql\n";
    
    $rs = mysql_query($sql, $this->link);
    if (!$rs)
    {
      $message = "MySQL error [".mysql_errno($this->link)."] ".mysql_error($this->link)."\n$sql";
      echo "\n$message\n\n";
    }
    return $rs;
  }
  
  function & select_all($sql)
  {
    $results = $this->execute($sql);
    $data    = array();
    
		if ($results and mysql_num_rows($results) > 0)
		{
      while ($row = mysql_fetch_row($results))
      {
        $result = array();
        foreach ($row as $idx => $value)
        {
#          $table  = mysql_field_table($results, $idx);
          $column = mysql_field_name($results, $idx);
#          $result[$table][$column] = $value;
          $result[$column] = $value;
        }
        $data[] = $result;
      }
    }
    
    mysql_free_result($results);
    return $data;
  }
  
  function & select_values($sql)
  {
    $results = $this->execute($sql);
    $data    = array();
    
		if ($results and mysql_num_rows($results) > 0)
		{
      while ($row = mysql_fetch_row($results)) {
        $data[] = $row[0];
      }
    }
    
    mysql_free_result($results);
    return $data;
  }
  
  function & columns($table)
  {
    $_table  = $this->quote_table($table);
    $results = $this->execute("DESC {$_table} ;");
    
    if (!$results) {
      throw new ActiveRecord_StatementInvalid("No such table: $table");
    }
    
    $columns = array();
		if (mysql_num_rows($results) > 0)
		{
      while ($row = mysql_fetch_row($results))
      {
        list($name, $type, $is_null, $key, $default,) = $row;
        $column = array();
        $type   = strtoupper($type);
        
        if (stripos($type, 'UNSIGNED') !== false)
        {
          $signed = false;
          $type = trim(str_ireplace('UNSIGNED', '', $type));
        }
        
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
        if (isset($signed))
        {
          $column['signed'] = $signed;
          unset($signed);
        }
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
  
  function select_database($database=null)
  {
    if (empty($database)) {
      $database = $this->config('database');
    }
    $success = mysql_select_db($database, $this->link);
    if (!$success) {
      throw new ActiveRecord_StatementInvalid("Can't select database $database.");
    }
    return $success;
  }
  
  
  # Renames a table.
  function rename_table($from, $to)
  {
    
  }
  
  # Adds a column to a table.
  function add_column()
  {
    
  }
  
  # Changes a column's definition in a table.
  function change_column()
  {
    
  }
  
  # Renames a column in a table.
  function rename_column()
  {
    
  }
  
  # Destroys a column in a table.
  function remove_column()
  {
    
  }
  
  
  function create_index()
  {
    
  }
  
  function remove_index()
  {
    
  }
  
  function insert($table, array $data, $returning=null)
  {
    $success = parent::insert($table, $data);
    
    if ($success and $returning !== null)
    {
      $returning = $this->quote_column($returning);
      $table     = $this->quote_table($table);
      return $this->select_value("SELECT MAX($returning) FROM $table LIMIT 1 ;");
    }
    
    return $success;
  }
  
  function update($table, $data, $conditions=null, $options=null)
  {
    $success = parent::update($table, $data, $conditions, $options);
    return $success ? mysql_affected_rows($this->link) : $success;
  }
  
  function delete($table, $conditions=null, $options=null)
  {
    $success = parent::delete($table, $conditions, $options);
    return $success ? mysql_affected_rows($this->link) : $success;
  }
}

?>
