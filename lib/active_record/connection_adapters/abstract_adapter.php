<?php
/**
 * Abstract adapter to build real ActiveRecord adapters.
 */
abstract class ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  public  $COLUMN_QUOTE = '"';
  public  $VALUE_QUOTE  = "'";
  public  $VALUE_FALSE  = 'f';
  public  $VALUE_TRUE   = 't';
  public  $VALUE_NULL   = 'NULL';
  public  $NATIVE_DATABASE_TYPES = array();
  
  protected $config;
  
  function __construct(array $config)
  {
    $this->config = $config;
    $this->connect();
  }
  
  # Returns the value of a configuration setting.
  function config($name)
  {
    return $this->config[$name];
  }
  
  # Connects to the database.
  abstract function connect();
  
  # Disconnects from the database.
  abstract function disconnect();
  
  # Checks wether the connection to the database is active or not.
  abstract function is_active();
  
  
  # Executes an SQL query.
  abstract function execute($sql);
  
  # Returns an array of hashes of columns => values.
  abstract function & select_all($sql);
  
  # Returns an array of values from the first column.
  abstract function & select_values($sql);
  
  # Returns the columns' definition of a table.
  abstract function & columns($sql);
  
  
  # Creates a database.
  abstract function create_database($database, array $options=null);
  
  # Destroys a database (and everything inside).
  abstract function drop_database($database);
  
  # Selects a default database to use.
  abstract function select_database($database=null);
  
  
  # Escapes a value to be used in a SQL query.
  abstract function escape_value($value);
  
  # Quotes a table name for use in a SQL query.
  function quote_table($table)
  {
    return $this->quote_column($table);
  }
  
  # Quotes a column name for use in a SQL query.
  function quote_column($column)
  {
    $column = trim($column);
    if (strpos($column, '.'))
    {
      $segments = explode('.', $column);
      foreach($segments as $i => $column)
      {
        if ($segments[$i] != '*') {
          $segments[$i] = $this->COLUMN_QUOTE.$column.$this->COLUMN_QUOTE;
        }
      }
      return implode('.', $segments);
    }
    return $this->COLUMN_QUOTE.$column.$this->COLUMN_QUOTE;
  }
  
  # Quotes a series of columns for use in a SQL query.
  function quote_columns($columns)
  {
    if (!is_array($columns)) {
      $columns = explode(',', $columns);
    }
    $columns = array_map(array($this, 'quote_column'), $columns);
    return implode(', ', $columns);
  }
  
  # Quotes and escapes a table name for use in a SQL query.
  function quote_value($value)
  {
    if($value === true) {
      return $this->VALUE_TRUE;
    }
    elseif($value === false) {
      return $this->VALUE_FALSE;
    }
    elseif($value === null) {
      return $this->VALUE_NULL;
    }
    elseif(is_array($value))
    {
      $value = array_map(array($this, 'quote_value'), $value);
      return '('.implode(', ', $value).')';
    }
    return $this->VALUE_QUOTE.$this->escape_value($value).$this->VALUE_QUOTE;
  }
  
  # Quotes a list of ORDER/GROUP BY columns.
  function sanitize_order($columns)
  {
    if (!is_array($columns)) {
      $columns = explode(',', $columns);
    }
    
    foreach($columns as $i => $column)
    {
      $column = trim($column);
      if (strpos($column, ' ') !== false)
      {
        list($column, $options) = explode(' ', $column, 2);
        $options = " $options";
      }
      else {
        $options = '';
      }
      $columns[$i] = $this->quote_column($column).$options;
    }
    
    return implode(', ', $columns);
  }
  
  # Creates a LIMIT + OFFSET SQL fragment.
  function sanitize_limit($limit=null, $page=null)
  {
    if(!$limit) {
      return '';
    }
    
    $limit = (int)$limit;
    $str   = "LIMIT $limit";
    if ($page)
    {
      $offset = ((int)$page - 1) * $limit;
      $str .= " OFFSET $offset";
    }
    return $str;
  }
  
  # Accepts an array, hash, or string of SQL assignments and
  # sanitizes them into a valid SQL fragment for a SET clause.
  function sanitize_sql_for_assignment($assignments)
  {
    if (is_hash($assignments)) {
      return $this->sanitize_sql_hash_for_assignment($assignments);
    }
    elseif (is_array($assignments)) {
      return $this->sanitize_sql_array($assignments);
    }
    return $assignments;
  }

  # Accepts an array, hash, or string of SQL conditions and
  # sanitizes them into a valid SQL fragment for a WHERE clause.
  # 
  # See sanitize_sql_array() and sanitize_sql_hash() for examples.
  # 
  function sanitize_sql_for_conditions($conditions)
  {
    if (is_hash($conditions)) {
      return $this->sanitize_sql_hash_for_conditions($conditions);
    }
    elseif (is_array($conditions)) {
      return $this->sanitize_sql_array($conditions);
    }
    return $conditions;
  }
  
  # Accepts an array of conditions. The array has each value
  # sanitized and interpolated into the SQL statement.
  # 
  #   sanitize_sql_array(array("name = :name AND post_id = :id",
  #     array('name' => 'toto', post_id => 59)));
  #   # => "name = 'toto'"
  #   
  #   sanitize_sql_array(array("name = '%s' AND group_id = %d", 'toto', 123));
  #   # => "name = 'toto' AND group_id = 123"
  # 
  function sanitize_sql_array($ary)
  {
    if (!isset($ary[1])) {
      return isset($ary[0]) ? $ary[0] : '';
    }
    if (is_hash($ary[1]))
    {
      # uses symbols
      $symbols = array();
      foreach($ary[1] as $symbol => $value) {
        $symbols[":$symbol"] = $this->quote_value($value);
      }
      $sql = strtr($ary[0], $symbols);
    }
    else
    {
      # uses markers
      for($i=1, $len = count($ary); $i<$len; $i++) {
          $ary[$i] = $this->escape_value($ary[$i], false);
      }
      $sql = call_user_func_array('sprintf', $ary);
    }
    return $sql;
  }
  
  # Sanitizes a hash of attribute/value pairs for use in
  # SQL conditions or assignments.
  #
  #   sanitize_sql_hash(array("a" => 'b', "c" => 'd'));
  #   # => array("\"a\" = 'b'", "\"c\" = 'd'")
  # 
  function & sanitize_sql_hash(array $hash)
  {
    $sanitized = array();
    foreach($hash as $f => $v)
    {
      $f  = $this->quote_column($f);
      $vv = $this->quote_value($v);
      $sanitized[] = is_array($v) ? "$f IN $vv" : "$f = $vv";
    }
    return $sanitized;
  }
  
  # Sanitizes a hash of attribute/value pairs into SQL
  # assignments for a SET clause.
  function sanitize_sql_hash_for_assignment(array $assignments)
  {
    $assignments = $this->sanitize_sql_hash($assignments);
    return implode(', ', $assignments);
  }
  
  # Sanitizes a hash of attribute/value pairs into SQL
  # conditions for a WHERE clause.
  function sanitize_sql_hash_for_conditions(array $conditions)
  {
    $conditions = $this->sanitize_sql_hash($conditions);
    return implode(' AND ', $conditions);
  }
  
  
  # Returns a helper to create a new table.
  #
  # Available options:
  # 
  # - temporary (bool), true to create a temporary table
  # - id        (bool), true to automatically add an auto incrementing id column
  # - options   (string), eg: "engine = innodb"
  # 
  function new_table($table, array $options=null)
  {
    return new ActiveRecord_Table($table, $options, $this);
  }
  
  /**
   * Creates a table.
   * 
   * Definition:
   * 
   * - temporary (bool), true to create a temporary table
   * - columns   (array), eg: {:name => {:type, :limit, :null, :default, :signed}}
   * - options   (string), eg: "engine = innodb"
   * - force     (null, bool), true: drop table before create, false: create if not exists.
   */
  function create_table($table, array $definition)
  {
    if (empty($definition['columns'])) {
      throw new MisagoException("Can't create table: there are no columns.", 500);
    }
    
    $columns = array();
    foreach($definition['columns'] as $name => $column) {
      $columns[] = $this->build_column_definition($name, $column);
    }
    
    $table     = $this->quote_table($table);
    $columns   = implode(', ', $columns);
    $temporary = (isset($definition['temporary']) and $definition['temporary']) ? 'TEMPORARY' : '';
    $if_not_exists = '';
    
    if (isset($definition['force']))
    {
      if ($definition['force']) {
        $this->execute("DROP $temporary TABLE IF EXISTS $table ;");
      }
      else {
        $if_not_exists = 'IF NOT EXISTS';
      }
    }
    
    $sql = "CREATE $temporary TABLE {$if_not_exists} $table ( $columns )";
    
    if (isset($definition['options'])) {
      $sql .= ' '.$definition['options'];
    }
    return $this->execute("$sql ;");
  }
  
  # Drops a table.
  # 
  # Options:
  # 
  # - temporary (bool), true if table to drop is a temporary table.
  # 
  function drop_table($table, array $options=null)
  {
    $table = $this->quote_table($table);
    $tmp   = (isset($options['temporary']) and $options['temporary']);
    return $this->execute("DROP ".($tmp ? "TEMPORARY" : '')." TABLE $table ;");
  }
  
  
  private function build_column_definition($name, array $column=null)
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
        $def .= " DEFAULT ".$this->quote_value($column['default']);
      }
    }
    else {
      $def = $this->NATIVE_DATABASE_TYPES[$column['type']];
    }
    
    $name = $this->quote_column($name);
    return "$name $def";
  }
  
  # Adds a column to a table.
  function add_column($table, $column, $type, array $options=null)
  {
    $definition = array(
      'type' => $type,
    );
    if (!empty($options)) {
      $definition = array_merge($definition, $options);
    }
    
    $table  = $this->quote_table($table);
    $column = $this->build_column_definition($column, $definition);
    return $this->execute("ALTER TABLE $table ADD $column ;");
  }
  
  # Removes a column from a table.
  function drop_column($table, $column)
  {
    $table  = $this->quote_table($table);
    $column = $this->quote_column($column);
    return $this->execute("ALTER TABLE $table DROP $column ;");
  }
  
  # Adds an index to a table column.
  # 
  # By default the index is named "$table_$column_idx" or "$table_$column_uniq".
  # 
  # Available options:
  # 
  # - type: null or unique
  # - name: manual naming of the index
  # - size: specified size of the index, in case of a blob/text. 
  # 
  # IMPROVE: Support indices on multiple columns.
  function add_index($table, $column, $options=null)
  {
    $type = isset($options['type']) ? $options['type'] : '';
    $name = isset($options['name']) ? $options['name'] :
      "{$table}_{$column}_".((strtolower($type) == 'unique') ? 'uniq' : 'idx');
    $size = isset($options['size']) ? "({$options['size']})" : '';
    
    $name   = $this->quote_column($name);
    $table  = $this->quote_table($table);
    $column = $this->quote_column($column);
    
    return $this->execute("CREATE $type INDEX $name ON $table($column$size) ;");
  }
  
  # Drops an index from a table.
  function drop_index($table, $name)
  {
    $table = $this->quote_table($table);
    $name  = $this->quote_column($name);
    return $this->execute("DROP INDEX $name ON $table ;");
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
  
  # Inserts a row in a table.
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
    
    return $this->execute("INSERT INTO $table ( $fields ) VALUES ( $values ) ;");
  }
  
  # Updates rows in a table.
  function update($table, $data, $conditions=null, $options=null)
  {
    $table = $this->quote_table($table);
    $sets  = $this->sanitize_sql_for_assignment($data);
    $where = empty($conditions)       ? '' : 'WHERE '.$this->sanitize_sql_for_conditions($conditions);
    $order = empty($options['order']) ? '' : "ORDER BY ".$this->sanitize_order($options['order']);
    $limit = empty($options['limit']) ? '' : $this->sanitize_limit($options['limit']);
    return $this->execute("UPDATE $table SET $sets $where $order $limit ;");
  }
  
  # Deletes rows from a table.
  function delete($table, $conditions=null, $options=null)
  {
    $table = $this->quote_table($table);
    $where = empty($conditions)       ? '' : 'WHERE '.$this->sanitize_sql_for_conditions($conditions);
    $order = empty($options['order']) ? '' : "ORDER BY ".$this->sanitize_order($options['order']);
    $limit = empty($options['limit']) ? '' : $this->sanitize_limit($options['limit']);
    return $this->execute("DELETE FROM $table $where $order $limit ;");
  }
  
  # Declares transaction state.
  # 
  # Available actions:
  # 
  # - begin: starts a transaction
  # - commit: ends a transaction and commits statements to the database.
  # - rollback: ends a transaction and drops statements (nothing is recorded in the database).
  # 
  function transaction($action)
  {
    switch(strtolower($action))
    {
      case 'begin':    return $this->execute("BEGIN ;");
      case 'commit':   return $this->execute("COMMIT ;");
      case 'rollback': return $this->execute("ROLLBACK ;");
      default: trigger_error("Unknown transaction : $action.", E_USER_WARNING);
    }
  }
  
  protected function report_error($sql, $message)
  {
    if (DEBUG > 1) {
      echo "\n$message\n$sql\n";
    }
    elseif (DEBUG) {
      misago_log("$message\n$sql\n");
    }
    else
    {
      # IMPROVE: Add some error logging for 'production' environment.
      
    }
  }
  
  protected function log_query($sql, $affected_rows, $time)
  {
    $message = sprintf("%s\nAffected rows: %d ; Elapsed time: %.02fms\n",
      $sql, $affected_rows, $time);
    
    if (DEBUG > 2) {
      echo "\n$message\n";
    }
    else {
      misago_log($message);
    }
  }
}

?>
