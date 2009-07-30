<?php

# PostgreSQL adapter.
class ActiveRecord_ConnectionAdapters_PostgresAdapter extends ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  public $NATIVE_DATABASE_TYPES = array(
    'primary_key' => "serial primary key",
    'string'      => array('name' => 'character varying', 'limit' => 255),
    'text'        => array('name' => 'text'),
    'integer'     => array('name' => 'integer'),
    'float'       => array('name' => 'float'),
    'decimal'     => array('name' => 'decimal'),
    'date'        => array('name' => 'date'),
    'time'        => array('name' => 'time'),
    'datetime'    => array('name' => 'timestamp'),
    'boolean'     => array('name' => 'boolean'),
    'binary'      => array('name' => 'bytea'),
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
    $this->link = $callback(implode(' ', $options), PGSQL_CONNECT_FORCE_NEW);
    
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
      $this->log_query($sql, pg_affected_rows($rs), $time);
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
    
		if ($results and pg_num_rows($results) > 0)
		{
      while ($row = pg_fetch_row($results)) {
        $data[] = $row[0];
      }
    }
    
    pg_free_result($results);
    return $data;
  }
  
  # TODO: Extract columns definition from database.
  function & columns($table)
  {
    $_table  = $this->quote_value($table);
    $columns = array();
    
    $results = $this->select_all("SELECT
      column_name, column_default, is_nullable, data_type, udt_name
      FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = {$_table} ;");
    
    print_r($results);
    
    foreach($results as $rs)
    {
      $column = array(
        'primary_key' => ($rs['column_default'] == "nextval('{$table}_{$rs['column_name']}_seq'::regclass)"),
        'type' => $rs['udt_name'],
#        'limit' => '',
        'null' => ($rs['is_nullable'] == 'YES'),
      );
      
      // ...
      
      $columns[$rs['column_name']] = $column;
    }
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
    
    $sql = "INSERT INTO $table ( $fields ) VALUES ( $values )";
    if (!empty($primary_key))
    {
      $primary_key = $this->quote_column($primary_key);
      $sql .= " RETURNING $primary_key";
    }
    $rs = $this->execute("$sql ;");
    
    if (!$rs or empty($primary_key)) {
      return $rs ? true : false;
    }
    
    if (pg_num_rows($rs))
    {
      $row = pg_fetch_row($rs);
      return (int)$row[0];
    }
    return false;
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
