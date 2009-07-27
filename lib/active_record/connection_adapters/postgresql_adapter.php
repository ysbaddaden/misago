<?php

# PostgreSQL adapter.
# 
# TEST: PostgreSQL adapter.
class ActiveRecord_ConnectionAdapters_PostgresqlAdapter extends ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  public $NATIVE_DATABASE_TYPES = array(
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
    return pg_escape_string($this->link, $value);
  }
  
  function is_active()
  {
    return (bool)$this->link;
  }
  
  function connect()
  {
    $callback = (isset($this->config['permanent']) and $this->config['permanent']) ?
      'pg_pconnect' : 'pg_connect';
    
    $options = array();
    if (!empty($this->config['host'])) {
      $options[] = 'host='.$this->config['host'];
    }
    if (!empty($this->config['username'])) {
      $options[] = 'user='.$this->config['username'];
    }
    if (!empty($this->config['password'])) {
      $options[] = 'user='.$this->config['password'];
    }
    if (!empty($this->config['database'])) {
      $options[] = 'dbname='.$this->config['database'];
    }
    $this->link = $callback(implode(' ', $options), true);
    
    if ($this->link === false) {
      throw new ActiveRecord_ConnectionNotEstablished("Unable to connect to PostgreSQL server.");
    }
  }
  
  function disconnect()
  {
    if ($this->link)
    {
      pg_close($this->link);
      $this->link = null;
    }
  }
  
  function execute($sql)
  {
    $time = microtime(true);
    $rs   = pg_query($this->link, $sql);
    $time = microtime(true) - $time;
    
    if (!$rs)
    {
      $message = "PostgreSQL error: ".pg_last_error($this->link);
      $this->report_error($sql, $message);
    }
    elseif (DEBUG) {
      $this->log_query($sql, pg_affected_rows($this->link), $time);
    }
    
    return $rs;
  }
  
  function & select_all($sql)
  {
    $results = $this->execute($sql);
    $data    = array();
    
		if ($results)
		{
      $data = pg_fetch_all($results);
      pg_free_result($results);
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
  
  # TODO: Extract columns definition from database.
  function & columns($table)
  {
    $_table  = $this->quote_table($table);
    $columns = array();
    
    // ...
    
    return $columns;
  }
  
  function create_database($database, array $options=null)
  {
    $database = $this->quote_table($database);
    $sql = "CREATE DATABASE $database";
    
    if (isset($options['charset']))
    {
      $charset = $this->quote_value($options['charset']);
      $sql .= " ENCODING = $charset";
    }
    return $this->execute("$sql ;");
  }
  
  function drop_database($database)
  {
    $database = $this->quote_table($database);
    return $this->execute("DROP DATABASE $database ;");
  }
  
  # NOTE: is there a way to select a database with postgresql?
  function select_database($database=null)
  {
    return true;
  }
  
  # FIXME: INSERT INTO x +RETURNING y+.
  function insert($table, array $data, $primary_key=null)
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
    
    $rs = $this->execute("INSERT INTO $table ( $fields ) VALUES ( $values ) RETURNING $primary_key ;");
    
    if (!$rs) {
      return false;
    }
    
    // ...
    
    return $id;
  }
  
  function update($table, $data, $conditions=null, $options=null)
  {
    $success = parent::update($table, $data, $conditions, $options);
    return $success ? pg_affected_rows($this->link) : $success;
  }
  
  function delete($table, $conditions=null, $options=null)
  {
    $success = parent::delete($table, $conditions, $options);
    return $success ? pg_affected_rows($this->link) : $success;
  }
}

?>
