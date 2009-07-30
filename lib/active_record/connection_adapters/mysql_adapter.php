<?php
# Mysql adapter.
# 
# See ActiveRecord_ConnectionAdapters_AbstractAdapter for documentation.
class ActiveRecord_ConnectionAdapters_MysqlAdapter extends ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  public  $COLUMN_QUOTE = '`';
  public  $NATIVE_DATABASE_TYPES = array(
    'primary_key' => "int(11) auto_increment PRIMARY KEY",
    'string'      => array('name' => 'varchar', 'limit' => 255),
    'text'        => array('name' => 'text'),
    'integer'     => array('name' => 'int',   'limit' => 4),
    'float'       => array('name' => 'float'),
    'decimal'     => array('name' => 'decimal'),
    'date'        => array('name' => 'date'),
    'time'        => array('name' => 'time'),
    'datetime'    => array('name' => 'datetime'),
#    'boolean'        => array('name' => 'tinyint', 'limit' => 1),
    'boolean'        => array('name' => 'boolean'),
    'binary'      => array('name' => 'blob'),
  );
  public  $VALUE_FALSE = '0';
  public  $VALUE_TRUE  = '1';
  private $link;
  
  function escape_value($value)
  {
    return mysql_real_escape_string($value, $this->link);
  }
  
  function is_active()
  {
    return (boolean)$this->link;
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
    $time = microtime(true);
    $rs   = mysql_query($sql, $this->link);
    $time = microtime(true) - $time;
    
    if (!$rs)
    {
      $message = "MySQL error [".mysql_errno($this->link)."] ".mysql_error($this->link);
      $this->report_error($sql, $message);
    }
    elseif (DEBUG) {
      $this->log_query($sql, mysql_affected_rows($this->link), $time);
    }
    
    return $rs;
  }
  
  function & select_all($sql)
  {
    $results = $this->execute($sql);
    $data    = array();
    
		if ($results)
		{
      while ($row = mysql_fetch_assoc($results)) {
        $data[] = $row;
      }
      mysql_free_result($results);
    }
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
        $column = array(
          'primary_key' => ($key === 'PRI'),
        );
        $type = strtolower($type);
        
        if (stripos($type, 'unsigned') !== false)
        {
          $signed = false;
          $type = trim(str_ireplace('unsigned', '', $type));
        }
        
        if (preg_match('/(\w+)\(([^\)]+)\)/', $type, $match))
        {
          $column['type']  = $match[1];
          $column['limit'] = (int)$match[2];
        }
        elseif ($type == 'tinytext')
        {
          $column['type']  = 'string';
          $column['limit'] = 255;
        }
        else {
          $column['type'] = $type;
        }
        
        if ($column['type'] == 'tinyint'
          and isset($column['limit'])
          and $column['limit'] == 1)
        {
          $column['type'] = 'boolean';
          unset($column['limit']);
        }
        else
        {
          foreach($this->NATIVE_DATABASE_TYPES as $type => $def)
          {
            if ($def['name'] == $column['type'])
            {
              $column['type'] = $type;
              break;
            }
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
  
  function insert($table, array $data, $primary_key=null)
  {
    $success = parent::insert($table, $data);
    if ($success and $primary_key !== null) {
      return mysql_insert_id($this->link);
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
