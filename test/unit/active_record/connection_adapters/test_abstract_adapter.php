<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/lib/unit_test.php";
require_once "$location/test/test_app/config/boot.php";

class FakeAdapter extends ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  public  $NATIVE_DATABASE_TYPES = array(
    'primary_key' => "SERIAL",
    'string'      => array('name' => 'VARCHAR', 'limit' => 255),
    'text'        => array('name' => 'TEXT'),
    'integer'     => array('name' => 'INT', 'limit' => 4),
    'float'       => array('name' => 'FLOAT'),
    'date'        => array('name' => 'DATE'),
    'time'        => array('name' => 'TIME'),
    'datetime'    => array('name' => 'DATETIME'),
    'bool'        => array('name' => 'BOOLEAN'),
    'binary'      => array('name' => 'BLOB'),
  );
  
  function execute($sql)
  {
    return preg_replace('/\s{2,}/', ' ', $sql);
  }
  
  function connect() {}
  function disconnect() {}
  
#  function & select_rows($sql) {}
  function & select_all($sql) {}
  function & select_one($sql) {}
  function & select_value($sql) {}
  function & select_values($sql) {}
  
  function & columns($sql) {}
  
  function is_active() {
    return true;
  }
  
  function escape_value($value) {
    return addslashes($value);
  }
}

class Test_ConnectionAdapter_AbstractAdapter extends Unit_Test
{
  function test_quote_table()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->quote_table('products');
    $this->assert_equal("", $test, '"products"');

    $test = $db->quote_table('misago.orders');
    $this->assert_equal("", $test, '"misago"."orders"');
  }

  function test_quote_column()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->quote_column('products');
    $this->assert_equal("", $test, '"products"');

    $test = $db->quote_column('misago.orders');
    $this->assert_equal("", $test, '"misago"."orders"');
  }

  function test_new_table()
  {
    $db = new FakeAdapter(array());
    
    $t = $db->new_table('products');
    $this->assert_true("new_table", $t instanceof ActiveRecord_Table);
    
    $this->assert_equal("must have a primary key", $t->columns, array(
      'id' => array('type' => 'primary_key'),
    ));
    
    $t = $db->new_table('products', array('primary_key' => "isbn"));
    $this->assert_equal("personalized primary key", $t->columns, array(
      'isbn' => array('type' => 'primary_key'),
    ));
    
    $t = $db->new_table('products', array('id' => false));
    $this->assert_equal("no primary key", $t->columns, array());
    
    $t = $db->new_table('products');
    $t->add_column('string', 'name');
    $this->assert_equal("add column", $t->columns, array(
      'id'   => array('type' => 'primary_key'),
      'name' => array('type' => 'string'),
    ));
    
    $t = $db->new_table('products', array('options' => 'ENGINE=innodb'));
    $t->add_column('string', 'name', array('limit' => 100));
    $t->add_column('text', 'description');
    $t->add_column('float', 'price', array('signed' => false));
    $this->assert_equal("add column with options", $t->columns, array(
      'id'   => array('type' => 'primary_key'),
      'name' => array('type' => 'string', 'limit' => 100),
      'description' => array('type' => 'text'),
      'price' => array('type' => 'float', 'signed' => false),
    ));
    
    $sql = $t->create();
    $this->assert_equal("", $sql, preg_replace('/\s+/', ' ', 'CREATE TABLE "products" (
      "id" SERIAL,
      "name" VARCHAR(100),
      "description" TEXT,
      "price" FLOAT UNSIGNED
    ) ENGINE=innodb ;'));
  }
  
  function test_sanitize_sql_array()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_array(array("a = a, b = b"));
    $this->assert_equal("empty values", $test, "a = a, b = b");
    
    $test = $db->sanitize_sql_array(array("a = :a, c = :c", array('a' => 'b', 'c' => 'd')));
    $this->assert_equal("with symbols", $test, "a = 'b', c = 'd'");
    
    $test = $db->sanitize_sql_array(array("a = :a, b = :b", array('a' => ':b', 'b' => ':a')));
    $this->assert_equal("with symbols (no recursion)", $test, "a = ':b', b = ':a'");
    
    $test = $db->sanitize_sql_array(array("a = '%s', c = %d", 'b', 4));
    $this->assert_equal("with printf markers", $test, "a = 'b', c = 4");
    
    $test = $db->sanitize_sql_array(array("a = '%s', c = %f", 'b', 3.2));
    $this->assert_equal("with printf markers (doubles)", $test, "a = 'b', c = 3.200000");
  }
  
  function test_sanitize_sql_hash()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_hash(array());
    $this->assert_equal("empty hash", $test, array());
    
    $test = $db->sanitize_sql_hash(array('a' => 'b', 'c' => 'd'));
    $this->assert_equal("simple hash", $test, array('"a" = \'b\'', '"c" = \'d\''));
  }

  function test_sanitize_sql_hash_for_conditions()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_hash_for_conditions(array('a' => 'b', 'c' => 'd'));
    $this->assert_equal("simple hash", $test, '"a" = \'b\' AND "c" = \'d\'');
  }

  function test_sanitize_sql_hash_for_assignment()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_hash_for_assignment(array('a' => 'b', 'c' => 'd'));
    $this->assert_equal("simple hash", $test, '"a" = \'b\', "c" = \'d\'');
  }

  function test_sanitize_sql_for_assignment()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_for_assignment(array());
    $this->assert_equal("empty array/hash", $test, "");
    
    $test = $db->sanitize_sql_for_assignment(array('a = b'));
    $this->assert_equal("array with empty values", $test, 'a = b');
    
    $test = $db->sanitize_sql_for_assignment(array("a = :a, c = :c", array('a' => 'b', 'c' => 'd')));
    $this->assert_equal("array with symbols", $test, "a = 'b', c = 'd'");
    
    $test = $db->sanitize_sql_for_assignment(array("a = :a, b = :b", array('a' => ':b', 'b' => ':a')));
    $this->assert_equal("array with symbols (no recursion)", $test, "a = ':b', b = ':a'");

    $test = $db->sanitize_sql_for_assignment(array("a = '%s', c = %d", 'b', 4));
    $this->assert_equal("array with printf markers", $test, "a = 'b', c = 4");
    
    $test = $db->sanitize_sql_for_assignment(array("a = '%s', c = %f", 'b', 3.2));
    $this->assert_equal("array with printf markers (doubles)", $test, "a = 'b', c = 3.200000");
  }
  
  function test_insert()
  {
    $db = new FakeAdapter(array());
    $sql = $db->insert('products', array('title' => 'azerty', 'created_at' => '2009-02-06'));
    $this->assert_equal("", $sql, "INSERT INTO \"products\" ( \"title\", \"created_at\" ) VALUES ( 'azerty', '2009-02-06' ) ;");
  }
  
  function test_update()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->update('products', array('title' => 'qwerty', 'updated_at' => '2009-02-08'));
    $this->assert_equal("no condition", $sql, "UPDATE \"products\" SET \"title\" = 'qwerty', \"updated_at\" = '2009-02-08' ;");
    
    $sql = $db->update('products', array('title' => 'qwerty', 'updated_at' => '2009-02-08'), array('id' => 123));
    $this->assert_equal("hash conditions", $sql, "UPDATE \"products\" SET \"title\" = 'qwerty', \"updated_at\" = '2009-02-08' WHERE \"id\" = '123' ;");
    
    $sql = $db->update('products', array('title' => 'qwerty', 'updated_at' => '2009-02-08'), 'id = 456');
    $this->assert_equal("string conditions", $sql, "UPDATE \"products\" SET \"title\" = 'qwerty', \"updated_at\" = '2009-02-08' WHERE id = 456 ;");
    
    $sql = $db->update('products', array('title = :title, updated_at = :updated_at', array('title' => 'qwerty', 'updated_at' => '2009-02-08')), 'id = 456');
    $this->assert_equal("symbols assignments", $sql, "UPDATE \"products\" SET title = 'qwerty', updated_at = '2009-02-08' WHERE id = 456 ;");
  }
  
  function test_delete()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->delete('products');
    $this->assert_equal("no condition", $sql, "DELETE FROM \"products\" ;");
    
    $sql = $db->delete('products', array('id' => 123));
    $this->assert_equal("hash condition", $sql, "DELETE FROM \"products\" WHERE \"id\" = '123' ;");
    
    $sql = $db->delete('products', 'id = 123');
    $this->assert_equal("string condition", $sql, "DELETE FROM \"products\" WHERE id = 123 ;");
  }
}

new Test_ConnectionAdapter_AbstractAdapter();

?>
