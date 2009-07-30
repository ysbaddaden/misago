<?php

$location = dirname(__FILE__).'/../../../..';
$_SERVER['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once "$location/lib/active_record/exception.php";
require_once "$location/lib/active_record/connection_adapters/postgres_adapter.php";

class Test_ConnectionAdapter_PostgresAdapter extends Unit_Test
{
  function test_connect()
  {
  /*
    $db = new ActiveRecord_ConnectionAdapters_PostgresAdapter(array(
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
  */
    $db = new ActiveRecord_ConnectionAdapters_PostgresAdapter(array(
      'host'     => 'localhost',
      'username' => 'postgres',
      'password' => '',
    ));
    $this->assert_true("connected by default", $db->is_active());
    
    $db->disconnect();
    $this->assert_false("disconnect", $db->is_active());

    $db->connect();
    $this->assert_true("connect", $db->is_active());
    
    $db->disconnect();
  }
  
  function test_execute()
  {
    $this->db = new ActiveRecord_ConnectionAdapters_PostgresAdapter(array(
      'host'     => 'localhost',
      'username' => 'postgres',
      'password' => '',
    ));
    
    $rs = $this->db->execute("SELECT version() ;");
    $this->assert_true("must return a resource", is_resource($rs));
  }
  
  function test_create_database()
  {
    $options = array("charset" => 'utf8');
    $rs = $this->db->create_database('misago_test', $options);
    $this->assert_true("", $rs ? true : false);
  }
  
  function test_create_table()
  {
    $options = array('options' => '');
    
    $t = $this->db->new_table("products", $options);
    $t->add_column('title', "string", array('limit' => 100, 'null' => false));
    $t->add_column('price', "double");
    $t->add_timestamps();
    $rs = $t->create();
    
    $this->assert_true("", $rs ? true : false);
  }
  
  /*
  function test_select_database()
  {
    try {
      $rs = $this->db->select_database('misago_fake_test');
      $rs = true;
    }
    catch(ActiveRecord_StatementInvalid $e) {
      $rs = false;
    }
    $this->assert_false("database doesn't exists", $rs);
    
    $rs = $this->db->select_database('misago_test');
    $this->assert_true("database exists", $rs);
  }
  */
  
  # TEST: Test missing table case.
  function test_columns()
  {
    $columns = $this->db->columns('products');
    
    $this->assert_equal("", $columns, array(
      'id'         => array('primary_key' => true,  'type' => 'integer',  'limit' => 11,  'null' => false),
      'title'      => array('primary_key' => false, 'type' => 'string',   'limit' => 100, 'null' => false),
      'price'      => array('primary_key' => false, 'type' => 'double',   'null' => true),
      'created_at' => array('primary_key' => false, 'type' => 'datetime', 'null' => true),
      'updated_at' => array('primary_key' => false, 'type' => 'datetime', 'null' => true),
    ));
  }
  
  /*
  function test_add_column()
  {
    $this->db->add_column('products', 'in_stock', 'bool');
    
    $columns = $this->db->columns('products');
    $this->assert_equal("", $columns, array(
      'id'         => array('primary_key' => true,  'type' => 'integer', 'limit' => 11,  'null' => false),
      'title'      => array('primary_key' => false, 'type' => 'string',  'limit' => 100, 'null' => false),
      'price'      => array('primary_key' => false, 'type' => 'double',   'null' => true, 'signed' => false),
      'created_at' => array('primary_key' => false, 'type' => 'datetime', 'null' => true),
      'updated_at' => array('primary_key' => false, 'type' => 'datetime', 'null' => true),
      'in_stock'   => array('primary_key' => false, 'type' => 'bool',     'null' => true),
    ));
  }
  */
  
  function test_insert()
  {
    $rs = $this->db->insert('products', array('title' => 'azerty'));
    $this->assert_true("must succeed", $rs);
    
    # disabled, because pg_query() triggers an annoying warning that you can't catch
#    $rs = $this->db->insert('products', array('released_at' => date('Y-m-d')));
#    $this->assert_false("must fail", $rs);
    
    $rs = $this->db->insert('products', array('title' => 'qwerty'));
    $this->assert_true("must succeed", $rs);
  }
  
  function test_select_all()
  {
    $rs = $this->db->select_all("SELECT title FROM products WHERE id = 1 ;");
    $this->assert_equal("", $rs, array(array('title' => 'azerty')));
    
    $rs = $this->db->select_all("SELECT title FROM products ;");
    $this->assert_equal("", $rs, array(
      array('title' => 'azerty'),
      array('title' => 'qwerty'),
    ));
  }
  
  function test_select_one()
  {
    $rs = $this->db->select_one("SELECT title FROM products WHERE id = 1 ;");
    $this->assert_equal("", $rs, array('title' => 'azerty'));
    
    $rs = $this->db->select_one("SELECT title FROM products LIMIT 1 OFFSET 1 ;");
    $this->assert_equal("", $rs, array('title' => 'qwerty'));
  }
  
  function test_select_value()
  {
    $rs = $this->db->select_value("SELECT title FROM products WHERE id = 1 ;");
    $this->assert_equal("", $rs, 'azerty');
  }
  
  function test_select_values()
  {
    $rs = $this->db->select_values("SELECT title FROM products ;");
    $this->assert_equal("", $rs, array('azerty', 'qwerty'));
  }
  
  function test_insert_returning()
  {
    $id = $this->db->insert('products', array('title' => 'qwerty'), 'id');
    $this->assert_equal("", $id, 3);
  }
  
  function test_drop_table()
  {
    $rs = $this->db->drop_table('products');
    $this->assert_true("", $rs ? true : false);
  }
  
  function test_drop_database()
  {
    $rs = $this->db->drop_database('misago_test');
    $this->assert_true("", $rs ? true : false);
  }
}

new Test_ConnectionAdapter_PostgresAdapter();

?>
