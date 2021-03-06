<?php
namespace Misago\ActiveRecord;

# Helper to create new tables. Generally used in migrations,
# but permits to create temporary tables on the fly too.
# 
#   $t = $this->connection->new_table('authors');
#   $t->add_column('name', 'string', array('limit' => 50));
#   $t->add_timestamps('date');
#   $t->create();
# 
# Create a temporary table, with no primary key:
# 
#   $t = $this->connection->new_table('stats', array('primary_key' => false, 'temporary' => true));
#   $t->add_column('counts', 'integer');
#   $t->create();
# 
class Table
{
  protected $connection;
  protected $db;
  
  public $name;
  public $columns = array();
  public $definitions = array(
    'id'          => true,
    'primary_key' => 'id',
  );
  
  # Constructor.
  # 
  # Options: 
  # 
  # - +id+        - true to automatically create the primary key column (default)
  # - +options+   - addition to table creation (eg: 'engine=myisam')
  # - +temporary+ - true to create a temporary table
  # - +force+     - true to drop table before create, otherwise creates if not exists
  # 
  function __construct($name, array $options=null, ConnectionAdapters\AbstractAdapter $connection)
  {
    $this->connection = $connection;
    $this->db = $this->connection;
    $this->name = $name;
    
    if (!empty($options)) {
      $this->definitions = array_merge($this->definitions, $options);
    }
    if (isset($this->definitions['id']) and $this->definitions['id']) {
      $this->add_column($this->definitions['primary_key'], 'primary_key');
    }
  }
  
  # Adds a column to table's definition.
  # 
  # Types:
  # 
  # - +primary_key+ (auto_increment integer, serial, ...)
  # - +string+
  # - +text+
  # - +integer+
  # - +float+
  # - +decimal+
  # - +date+
  # - +time+
  # - +datetime+
  # - +boolean+
  # - +binary+
  # 
  # Options:
  # 
  # - +primary_key+ - true if column is the primary key
  # - +null+    - can the column be null? (default: true)
  # - +limit+   - maximum size
  # - +default+ - default value
  # - +signed+  - is the integer/float column signed?
  # 
  # Examples:
  # 
  #   $t->add_column('name',  'string',  array('limit' => 50));
  #   $t->add_column('price', 'numeric');
  # 
  function add_column($column, $type, array $options=null)
  {
    $definition = array(
      'type' => $type,
    );
    if (!empty($options)) {
      $definition = array_merge($definition, $options);
    }
    
    if (isset($this->columns[$column])) {
      trigger_error("Column $column already defined (overwriting it).", E_USER_WARNING);
    }
    
    $this->columns[$column] = $definition;
  }
  
  # Adds timestamp columns to table's definition.
  # 
  # Types:
  # 
  # - +date+     - will add +created_on+ & +updated_on+.
  # - +time+     - will add +created_at+ & +updated_at+.
  # - +datetime+ - will add +created_at+ & +updated_at+.
  # 
  function add_timestamps($type='datetime')
  {
    switch($type)
    {
      case 'date':
        $this->add_column('created_on', $type);
        $this->add_column('updated_on', $type);
      break;
      
      case 'time':
      case 'datetime':
      default:
        $this->add_column('created_at', $type);
        $this->add_column('updated_at', $type);
    }
  }
  
  # Actually creates the table in database.
  function create()
  {
    $this->definitions['columns'] =& $this->columns;
    return $this->connection->create_table($this->name, $this->definitions);
  }
}

?>
