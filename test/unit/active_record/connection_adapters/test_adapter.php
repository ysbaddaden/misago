<?php
require_once __DIR__.'/../../../unit.php';

class Test_ConnectionAdapter_Adapter extends Misago\Unit\TestCase
{
  /*
  function test_connect()
  {
    $db = new Misago\ActiveRecord\ConnectionAdapters\PostgresqlAdapter(array(
      'host'     => 'localhost',
      'username' => 'wrong_user',
      'password' => 'wrong_password',
    ));
    try
    {
      $db->connect();
      $failed = false;
    }
    catch(ActiveRecord_ConnectionNotEstablished $e) {
      $failed = true;
    }
    $this->assert_true("Connection failed.", $failed);
  
    $db = new Misago\ActiveRecord\ConnectionAdapters\PostgresqlAdapter(array(
      'host'     => 'localhost',
      'username' => 'postgres',
      'password' => '',
    ));
    $this->assert_false($db->is_active(), "not connected by default");
    
    $db->connect();
    $this->assert_true($db->is_active(), "connect");
    
    $db->disconnect();
    $this->assert_false($db->is_active(), "disconnect");
  }
  
  function test_select_database()
  {
    try {
      $rs = $this->db->select_database('misago_fake_test');
      $rs = true;
    }
    catch(Misago\ActiveRecord\StatementInvalid $e) {
      $rs = false;
    }
    $this->assert_false($rs, "database doesn't exists");
    
    $rs = $this->db->select_database('misago_test');
    $this->assert_true($rs, "database exists");
  }
  */
  
  function setup()
  {
    $this->db = Misago\ActiveRecord\Connection::get($_SERVER['MISAGO_ENV']);
  }
  
  function test_execute()
  {
    $rs = $this->db->execute("SELECT VERSION() ;");
    $this->assert_true(is_resource($rs));
  }
  
  function test_create_database()
  {
    $options = array("charset" => 'utf8');
    $rs = $this->db->create_database('misago_test', $options);
    $this->assert_true($rs ? true : false);
  }
  
  function test_database_exists()
  {
    $this->assert_true($this->db->database_exists('misago_test'));
    $this->assert_false($this->db->database_exists('some_unknown_database'));
  }
  
  function test_drop_database()
  {
    $rs = $this->db->drop_database('misago_test');
    $this->assert_true($rs ? true : false);
  }
  
  function test_create_table()
  {
    $options = array('options' => '');
    
    $t = $this->db->new_table("objects", $options);
    $t->add_column('title',       "string", array('limit' => 100, 'null' => false));
    $t->add_column('price',       "decimal");
    $t->add_column('a_float',     "float"/*, array('limit' => 8)*/);
    $t->add_column('released_on', "date");
    $t->add_column('last_visit',  "time");
    $t->add_column('image',       "binary");
    $t->add_timestamps();
    $rs = $t->create();
    
    $this->assert_true($rs ? true : false);
  }
  
  function test_columns_for_unknown_table()
  {
    try
    {
      $columns = $this->db->columns('some_unknown_table');
      $failed  = false;
    }
    catch(\Misago\ActiveRecord\StatementInvalid $e) {
      $failed = true;
    }
    $this->assert_true($failed);
  }
  
  function test_columns()
  {
    $columns = $this->db->columns('objects');
    
    $this->assert_equal($columns, array(
      'id'          => array('primary_key' => true, 'type' => 'integer', 'null' => false, 'limit' => 4),
      'title'       => array('type' => 'string',   'null' => false, 'limit' => 100),
      'price'       => array('type' => 'decimal',  'null' => true),
      'a_float'     => array('type' => 'float',    'null' => true/*, 'limit' => 8*/),
      'released_on' => array('type' => 'date',     'null' => true),
      'last_visit'  => array('type' => 'time',     'null' => true),
      'image'       => array('type' => 'binary',   'null' => true),
      'created_at'  => array('type' => 'datetime', 'null' => true),
      'updated_at'  => array('type' => 'datetime', 'null' => true),
    ));
  }
  
  function test_add_column()
  {
    $this->db->add_column('objects', 'in_stock', 'boolean');
    
    $columns = $this->db->columns('objects');
    $this->assert_equal($columns, array(
      'id'          => array('primary_key' => true, 'type' => 'integer', 'null' => false, 'limit' => 4),
      'title'       => array('type' => 'string',   'limit' => 100, 'null' => false),
      'price'       => array('type' => 'decimal',  'null' => true),
      'a_float'     => array('type' => 'float',    'null' => true/*, 'limit' => 8*/),
      'released_on' => array('type' => 'date',     'null' => true),
      'last_visit'  => array('type' => 'time',     'null' => true),
      'image'       => array('type' => 'binary',   'null' => true),
      'created_at'  => array('type' => 'datetime', 'null' => true),
      'updated_at'  => array('type' => 'datetime', 'null' => true),
      'in_stock'    => array('type' => 'boolean',  'null' => true),
    ));
  }
  
  function test_insert()
  {
    $rs = $this->db->insert('objects', array('title' => 'azerty'));
    $this->assert_true($rs);
    
    # disabled, because pg_query() triggers an annoying warning that you can't catch
#    $rs = $this->db->insert('objects', array('released_at' => date('Y-m-d')));
#    $this->assert_false("must fail", $rs);
    
    $rs = $this->db->insert('objects', array('title' => 'qwerty'));
    $this->assert_true($rs);
  }
  
  function test_select_all()
  {
    $rs = $this->db->select_all("SELECT title FROM objects WHERE id = 1 ;");
    $this->assert_equal($rs, array(array('title' => 'azerty')));
    
    $rs = $this->db->select_all("SELECT title FROM objects ;");
    $this->assert_equal($rs, array(
      array('title' => 'azerty'),
      array('title' => 'qwerty'),
    ));
  }
  
  function test_select_one()
  {
    $rs = $this->db->select_one("SELECT title FROM objects WHERE id = 1 ;");
    $this->assert_equal($rs, array('title' => 'azerty'));
    
    $rs = $this->db->select_one("SELECT title FROM objects LIMIT 1 OFFSET 1 ;");
    $this->assert_equal($rs, array('title' => 'qwerty'));
  }
  
  function test_select_value()
  {
    $rs = $this->db->select_value("SELECT title FROM objects WHERE id = 1 ;");
    $this->assert_equal($rs, 'azerty');
  }
  
  function test_select_values()
  {
    $rs = $this->db->select_values("SELECT title FROM objects ;");
    $this->assert_equal($rs, array('azerty', 'qwerty'));
  }
  
  function test_insert_returning()
  {
    $id = $this->db->insert('objects', array('title' => 'qwerty'), 'id');
    $this->assert_equal($id, 3);
  }
  
  function test_table_exists()
  {
    $this->assert_true($this->db->table_exists('objects'));
    $this->assert_false($this->db->table_exists('unknown_table'));
  }
  
  function test_drop_table()
  {
    $rs = $this->db->drop_table('objects');
    $this->assert_true($rs ? true : false);
  }
}

?>
