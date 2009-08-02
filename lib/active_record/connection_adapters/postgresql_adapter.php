<?php

# PostgreSQL adapter.
#
# Help on information_schema: http://www.alberton.info/postgresql_meta_info.html
# 
# Note: PostgreSQL client library isn't capable to switch between
#       databases. You need to reconnect to achieve this.
class ActiveRecord_ConnectionAdapters_PostgresqlAdapter extends ActiveRecord_ConnectionAdapters_AbstractAdapter
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
  public  $VALUE_FALSE  = "FALSE";
  public  $VALUE_TRUE   = "TRUE";
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
    $this->pg_connect();
  }
  
  private function pg_connect($database=null)
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
    if (!empty($database)) {
      $options[] = 'dbname='.$database;
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
  
  function select_database($database=null)
  {
    $this->disconnect();
    
    if (empty($database)) {
      $database = $this->config('database');
    }
    return $this->pg_connect($database);
  }
  
  function table_exists($table_name)
  {
    $rs = $this->select_value("SELECT COUNT(*)
      FROM information_schema.tables
      WHERE table_name = ".$this->quote_value($table_name)." ;");
    return ($rs > 0);
  }
  
  function database_exists($database)
  {
    $rs = $this->select_value("SELECT COUNT(*)
      FROM pg_catalog.pg_database
      WHERE datname = ".$this->quote_value($database)." ;");
    return ($rs > 0);
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
    $data = array();
    
		if ($results)
		{
		  if (pg_num_rows($results) > 0) {
        $data = pg_fetch_all($results);
      }
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
  
  function & columns($table)
  {
    $_table  = $this->quote_value($table);
    $columns = array();
    
    $results = $this->select_all("SELECT column_name, udt_name, is_nullable, character_maximum_length
      FROM information_schema.columns
      WHERE table_name = {$_table} ;");
    
    if (!$results) {
      throw new ActiveRecord_StatementInvalid("No such table: $table");
    }
    
    $pk_results = $this->select_values("SELECT column_name
      FROM information_schema.constraint_column_usage
      WHERE table_name = {$_table} AND constraint_name = ".$this->quote_value("{$table}_pkey")." ;");
    
    foreach($results as $rs)
    {
      $column = array(
        'null' => ($rs['is_nullable'] == 'YES'),
      );
      
      # primary_key?
      if (in_array($rs['column_name'], $pk_results)) {
        $column['primary_key'] = true;
      }
      
      # type
      switch($rs['udt_name'])
      {
        case 'int2': $column['type'] = 'integer'; $column['limit'] = 2; break;
        case 'int4': $column['type'] = 'integer'; $column['limit'] = 4; break;
        case 'int8': $column['type'] = 'integer'; $column['limit'] = 8; break;
        
        case 'numeric': $column['type'] = 'decimal'; break;
        
        case 'float4': $column['type'] = 'float'; /*$column['limit'] = 4;*/ break;
        case 'float8': $column['type'] = 'float'; /*$column['limit'] = 8;*/ break;
        
        case 'char':  case 'varchar': case 'text':
        case 'point': case 'line': case 'lseg': case 'box': case 'polygon': case 'circle': case 'path':
        case 'cidr':  case 'inet': case 'macaddr':
        case 'bit':   case 'varbit':
        case 'xml':
          $column['type'] = 'string';
          if (isset($rs['character_maximum_length'])) {
            $column['limit'] = $rs['character_maximum_length'];
          }
          break;
        
        case 'timestamp': $column['type'] = 'datetime'; break;
        case 'date':      $column['type'] = 'date';     break;
        case 'time':      $column['type'] = 'time';     break;
        
        case 'bool':  $column['type'] = 'boolean'; break;
        case 'bytea': $column['type'] = 'binary';  break;
      }
      
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
    if (!empty($options['order']) or !empty($options['limit'])) {
      $conditions = $this->build_order_limit_subselect($table, $conditions, $options);
    }
    $success = parent::update($table, $data, $conditions);
    return $success ? pg_affected_rows($success) : $success;
  }
  
  function delete($table, $conditions=null, $options=null)
  {
    if (!empty($options['order']) or !empty($options['limit'])) {
      $conditions = $this->build_order_limit_subselect($table, $conditions, $options);
    }
    $success = parent::delete($table, $conditions);
    return $success ? pg_affected_rows($success) : $success;
  }
  
  private function build_order_limit_subselect($table, $conditions, $options)
  {
    $pk    = $this->quote_column($options['primary_key']);
    $where = empty($conditions)       ? '' : 'WHERE '.$this->sanitize_sql_for_conditions($conditions);
    $order = empty($options['order']) ? '' : "ORDER BY ".$this->sanitize_order($options['order']);
    $limit = empty($options['limit']) ? '' : $this->sanitize_limit($options['limit']);
    return "$pk IN (SELECT $pk FROM ".$this->quote_table($table)." $where $order $limit)";
  }
}

?>
