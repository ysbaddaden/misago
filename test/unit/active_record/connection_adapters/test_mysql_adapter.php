<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['environment'] = 'test';

require_once "$location/lib/unit_test.php";
require_once "$location/test/test_app/config/boot.php";

# TODO: Test if MysqlAdapter::create_table() actually creates the table.
class Test_ConnectionAdapter_MysqlAdapter extends Unit_Test
{
  function test_connect()
  {
  /*
    $db = new ActiveRecord_ConnectionAdapters_MysqlAdapter(array(
      'host'     => 'localhost',
      'username' => 'wrong_user',
      'password' => 'wrong_password',
    ));
    try
    {
      $db->connect();
      $failed = false;
    }
    catch(MisagoException $e) {
      $failed = true;
    }
    $this->assert_true("Connection failed.", $failed);
  */
    $db = new ActiveRecord_ConnectionAdapters_MysqlAdapter(array(
      'host'     => 'localhost',
      'username' => 'root',
      'password' => '',
    ));
    
    $db->connect();
    $this->assert_true("connect", $db->is_active());
    
    $db->disconnect();
    $this->assert_false("disconnect", $db->is_active());
  }

  function test_execute()
  {
    $this->db = new ActiveRecord_ConnectionAdapters_MysqlAdapter(array(
      'host'     => 'localhost',
      'username' => 'root',
      'password' => '',
    ));
    
    $rs = $this->db->execute("SHOW DATABASES ;");
    $this->assert_true("must return a resource", is_resource($rs));
  }
  
  function test_create_and_drop_database()
  {
    $options = array("charset" => 'utf8');
    $rs = $this->db->create_database('misago_test', $options);
    $this->assert_true("", $rs ? true : false);
  }
  
  function test_create_table()
  {
    $options = array('options' => 'ENGINE=innodb');
    
    $t = $this->db->new_table("misago_test.products", $options);
    $t->add_column("string", 'title', array('limit' => 100));
    $t->add_timestamps();
    $rs = $t->create();
    
    $this->assert_true("", $rs ? true : false);
  }
  
  function test_drop_table()
  {
    $rs = $this->db->drop_table('misago_test.products');
    $this->assert_true("", $rs ? true : false);
  }
  
  function test_drop_database()
  {
    $rs = $this->db->drop_database('misago_test');
    $this->assert_true("", $rs ? true : false);
  }
  
}

new Test_ConnectionAdapter_MysqlAdapter();

?>
