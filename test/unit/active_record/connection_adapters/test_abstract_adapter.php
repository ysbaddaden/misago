<?php

$location = dirname(__FILE__).'/../../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";
require_once 'active_record/exception.php';

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
    if (isset($_SERVER['MISAGO_DEBUG']) and $_SERVER['MISAGO_DEBUG'] >= 2) {
      echo "\n$sql";
    }
    return preg_replace('/\s{2,}/', ' ', $sql);
  }
  
  function connect() {}
  function disconnect() {}
  
#  function & select_rows($sql) {}
  function & select_all($sql) {}
  function & select_one($sql) {}
  function & select_values($sql) {}
  
  function & columns($sql) {}
  
  function is_active() {
    return true;
  }
  
  function escape_value($value) {
    return addslashes($value);
  }
  
  function create_database($database, array $options=null) {}
  function drop_database($database) {}
  function select_database($database=null) {}
  
  function table_exists($table_name) {}
  function database_exists($database) {}
}

class Test_ConnectionAdapter_AbstractAdapter extends Unit_Test
{
  function test_quote_table()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->quote_table('products');
    $this->assert_equal($test, '"products"');

    $test = $db->quote_table('misago.orders');
    $this->assert_equal($test, '"misago"."orders"');
  }

  function test_quote_column()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_order('*');
    $this->assert_equal(trim($test), '*');
    
    $test = $db->quote_column('products');
    $this->assert_equal($test, '"products"');

    $test = $db->quote_column('misago.orders');
    $this->assert_equal($test, '"misago"."orders"');

    $test = $db->quote_column('products.*');
    $this->assert_equal($test, '"products".*');

    $test = $db->quote_column('COUNT(story_id)');
    $this->assert_equal($test, 'COUNT("story_id")');

    $test = $db->quote_column('sum(paypal.montant)');
    $this->assert_equal($test, 'sum("paypal"."montant")');

    $test = $db->sanitize_order('COUNT(*)');
    $this->assert_equal(trim($test), 'COUNT(*)');
  }
  
  function test_quote_columns()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->quote_columns('id, name');
    $this->assert_equal($test, '"id", "name"');

    $test = $db->quote_columns('misago.orders, products, tags');
    $this->assert_equal($test, '"misago"."orders", "products", "tags"');

    $test = $db->quote_columns('COUNT(paypal.id), paypal.date, sum(*)');
    $this->assert_equal($test, 'COUNT("paypal"."id"), "paypal"."date", sum(*)');

    $test = $db->quote_columns('COUNT(distinct(id))');
    $this->assert_equal($test, 'COUNT(distinct("id"))');
  }
  
  function test_quote_value()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->quote_value('azerty');
    $this->assert_equal($test, "'azerty'");
    
    $test = $db->quote_value('1.0');
    $this->assert_equal($test, "'1.0'");
    
    $test = $db->quote_value('1');
    $this->assert_equal($test, "'1'");
    
    $test = $db->quote_value(true);
    $this->assert_equal($test, 't');
    
    $test = $db->quote_value(false);
    $this->assert_equal($test, 'f');
    
    $test = $db->quote_value(null);
    $this->assert_equal($test, 'NULL');
    
    $test = $db->quote_value(array('a', true, 'b', 1));
    $this->assert_equal($test, "('a', t, 'b', '1')");
  }
  
  function test_create_table()
  {
    $db = new FakeAdapter(array());
    
    $columns = array(
      'id'   => array('type' => 'integer', 'signed' => false),
      'name' => array('type' => 'string'),
    );
    
    $definition = array('columns' => $columns);
    $sql = $db->create_table('products', $definition);
    $this->assert_equal($sql, "CREATE TABLE \"products\" ( \"id\" INT(4) UNSIGNED, \"name\" VARCHAR(255) ) ;");
    
    $definition = array('columns' => $columns, 'temporary' => true);
    $sql = $db->create_table('products', $definition);
    $this->assert_equal($sql, "CREATE TEMPORARY TABLE \"products\" ( \"id\" INT(4) UNSIGNED, \"name\" VARCHAR(255) ) ;");
    
    $definition = array('columns' => $columns, 'temporary' => true, 'force' => false);
    $sql = $db->create_table('products', $definition);
    $this->assert_equal($sql, "CREATE TEMPORARY TABLE IF NOT EXISTS \"products\" ( \"id\" INT(4) UNSIGNED, \"name\" VARCHAR(255) ) ;");
    
    $definition = array('columns' => $columns, 'temporary' => true, 'force' => true);
    $sql = $db->create_table('products', $definition);
    $this->assert_equal($sql, "CREATE TEMPORARY TABLE \"products\" ( \"id\" INT(4) UNSIGNED, \"name\" VARCHAR(255) ) ;");
    
    $definition = array('columns' => $columns, 'force' => true);
    $sql = $db->create_table('products', $definition);
    $this->assert_equal($sql, "CREATE TABLE \"products\" ( \"id\" INT(4) UNSIGNED, \"name\" VARCHAR(255) ) ;");
    
    $definition = array('columns' => array('id' => array('type' => 'string', 'limit' => 2, 'primary_key' => true)));
    $sql = $db->create_table('products', $definition);
    $this->assert_equal($sql, "CREATE TABLE \"products\" ( \"id\" VARCHAR(2) PRIMARY KEY ) ;");
  }
  
  function test_new_table()
  {
    $db = new FakeAdapter(array());
    
    $t = $db->new_table('products');
    $this->assert_true($t instanceof ActiveRecord_Table);
    $this->assert_equal($t->columns, array('id' => array('type' => 'primary_key')));
    
    $t = $db->new_table('products', array('primary_key' => "isbn"));
    $this->assert_equal($t->columns, array('isbn' => array('type' => 'primary_key')));
    
    $t = $db->new_table('products', array('id' => false));
    $this->assert_equal($t->columns, array());
    
    $t = $db->new_table('products');
    $t->add_column('name', 'string');
    $this->assert_equal($t->columns, array(
      'id'   => array('type' => 'primary_key'),
      'name' => array('type' => 'string'),
    ));
    
    $t = $db->new_table('products', array('options' => 'ENGINE=innodb'));
    $t->add_column('name', 'string', array('limit' => 100));
    $t->add_column('description', 'text');
    $t->add_column('price', 'float', array('signed' => false));
    $this->assert_equal($t->columns, array(
      'id'   => array('type' => 'primary_key'),
      'name' => array('type' => 'string', 'limit' => 100),
      'description' => array('type' => 'text'),
      'price' => array('type' => 'float', 'signed' => false),
    ));
    
    $sql = $t->create();
    $this->assert_equal($sql, preg_replace('/\s{2,}/', ' ', 'CREATE TABLE "products" (
      "id" SERIAL,
      "name" VARCHAR(100),
      "description" TEXT,
      "price" FLOAT UNSIGNED
    ) ENGINE=innodb ;'));
    
  }
  
  function test_add_column()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->add_column('products', 'in_stock', 'bool');
    $this->assert_equal($sql, 'ALTER TABLE "products" ADD "in_stock" BOOLEAN ;');
    
    $sql = $db->add_column('products', 'in_stock', 'bool', array('null' => false, 'default' => true));
    $this->assert_equal($sql, 'ALTER TABLE "products" ADD "in_stock" BOOLEAN NOT NULL DEFAULT t ;');
  }
  
  function test_drop_column()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->drop_column('products', 'in_stock');
    $this->assert_equal($sql, 'ALTER TABLE "products" DROP "in_stock" ;');
  }
  
  function test_add_index()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->add_index('products', 'in_stock');
    $this->assert_equal($sql, 'CREATE INDEX "products_in_stock_idx" ON "products"("in_stock") ;');
    
    $sql = $db->add_index('products', 'name', array('type' => 'unique'));
    $this->assert_equal($sql, 'CREATE unique INDEX "products_name_uniq" ON "products"("name") ;');
    
    $sql = $db->add_index('products', 'name', array('size' => '10'));
    $this->assert_equal($sql, 'CREATE INDEX "products_name_idx" ON "products"("name"(10)) ;');
    
    $sql = $db->add_index('products', 'name', array('type' => 'unique', 'size' => 5));
    $this->assert_equal($sql, 'CREATE unique INDEX "products_name_uniq" ON "products"("name"(5)) ;');
    
    $sql = $db->add_index('products', 'name', array('name' => 'product_uniq_idx', 'type' => 'unique', 'size' => 5));
    $this->assert_equal($sql, 'CREATE unique INDEX "product_uniq_idx" ON "products"("name"(5)) ;');
  }
  
  function test_drop_index()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->drop_index('products', 'product_uniq_idx');
    $this->assert_equal($sql, 'DROP INDEX "product_uniq_idx" ON "products" ;');
    
    $sql = $db->drop_index('products', 'product_name_uniq');
    $this->assert_equal($sql, 'DROP INDEX "product_name_uniq" ON "products" ;');
  }
  
  function test_sanitize_order()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_order('toto');
    $this->assert_equal(trim($test), '"toto"');
    
    $test = $db->sanitize_order('toto asc');
    $this->assert_equal(trim($test), '"toto" asc');
    
    $test = $db->sanitize_order('toto.titi DESC');
    $this->assert_equal(trim($test), '"toto"."titi" DESC');
    
    $test = $db->sanitize_order('toto.titi DESC, tata asc');
    $this->assert_equal(trim($test), '"toto"."titi" DESC, "tata" asc');
    
    $test = $db->sanitize_order('toto.titi, tata asc');
    $this->assert_equal(trim($test), '"toto"."titi", "tata" asc');
    
    $test = $db->sanitize_order('rand()');
    $this->assert_equal(trim($test), 'rand()');
    
    $test = $db->sanitize_order('count(toto.titi)');
    $this->assert_equal(trim($test), 'count("toto"."titi")');
    
    $test = $db->sanitize_order('count(*)');
    $this->assert_equal(trim($test), 'count(*)');
    
    $test = $db->sanitize_order('count(toto.story_id)');
    $this->assert_equal(trim($test), 'count("toto"."story_id")');
    
#    $test = $db->sanitize_order('ADD_DATE(toto.created_at, toto.updated_at)');
#    $this->assert_equal(trim($test), 'ADD_DATE("toto"."created_at", "toto"."updated_at")');
  }
  
  function test_sanitize_limit()
  {
    $db = new FakeAdapter(array());
    
    $limit = $db->sanitize_limit(10);
    $this->assert_equal($limit, "LIMIT 10");
    
    $limit = $db->sanitize_limit(10, 2);
    $this->assert_equal($limit, "LIMIT 10 OFFSET 10");
  }
  
  function test_sanitize_sql_array()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_array(array("a = a, b = b"));
    $this->assert_equal($test, "a = a, b = b");
    
    $test = $db->sanitize_sql_array(array("a = :a, c = :c", array('a' => 'b', 'c' => 'd')));
    $this->assert_equal($test, "a = 'b', c = 'd'");
    
    $test = $db->sanitize_sql_array(array("a = :a, b = :b", array('a' => ':b', 'b' => ':a')));
    $this->assert_equal($test, "a = ':b', b = ':a'");
    
    $test = $db->sanitize_sql_array(array("a = '%s', c = %d", 'b', 4));
    $this->assert_equal($test, "a = 'b', c = 4");
    
    $test = $db->sanitize_sql_array(array("a = '%s', c = %f", 'b', 3.2));
    $this->assert_equal($test, "a = 'b', c = 3.200000");
  }
  
  function test_sanitize_sql_hash()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_hash(array());
    $this->assert_equal($test, array());
    
    $test = $db->sanitize_sql_hash(array('a' => 'b', 'c' => 'd'));
    $this->assert_equal($test, array('"a" = \'b\'', '"c" = \'d\''));
  }

  function test_sanitize_sql_hash_for_conditions()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_hash_for_conditions(array('a' => 'b', 'c' => 'd'));
    $this->assert_equal($test, '"a" = \'b\' AND "c" = \'d\'');
    
    $test = $db->sanitize_sql_hash_for_conditions(array('a' => 'b', 'c' => array(1, 2, 3)));
    $this->assert_equal($test, '"a" = \'b\' AND "c" IN (\'1\', \'2\', \'3\')');
  }

  function test_sanitize_sql_hash_for_assignment()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_hash_for_assignment(array('a' => 'b', 'c' => 'd'));
    $this->assert_equal($test, '"a" = \'b\', "c" = \'d\'');
  }

  function test_sanitize_sql_for_assignment()
  {
    $db = new FakeAdapter(array());
    
    $test = $db->sanitize_sql_for_assignment(array());
    $this->assert_equal($test, "");
    
    $test = $db->sanitize_sql_for_assignment(array('a = b'));
    $this->assert_equal($test, 'a = b');
    
    $test = $db->sanitize_sql_for_assignment(array("a = :a, c = :c", array('a' => 'b', 'c' => 'd')));
    $this->assert_equal($test, "a = 'b', c = 'd'");
    
    $test = $db->sanitize_sql_for_assignment(array("a = :a, b = :b", array('a' => ':b', 'b' => ':a')));
    $this->assert_equal($test, "a = ':b', b = ':a'");

    $test = $db->sanitize_sql_for_assignment(array("a = '%s', c = %d", 'b', 4));
    $this->assert_equal($test, "a = 'b', c = 4");
    
    $test = $db->sanitize_sql_for_assignment(array("a = '%s', c = %f", 'b', 3.2));
    $this->assert_equal($test, "a = 'b', c = 3.200000");
  }
  
  function test_insert()
  {
    $db = new FakeAdapter(array());
    $sql = $db->insert('products', array('title' => 'azerty', 'created_at' => '2009-02-06'));
    $this->assert_equal($sql, "INSERT INTO \"products\" ( \"title\", \"created_at\" ) VALUES ( 'azerty', '2009-02-06' ) ;");
  }
  
  function test_update()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->update('products', array('title' => 'qwerty', 'updated_at' => '2009-02-08'));
    $this->assert_equal($sql, "UPDATE \"products\" SET \"title\" = 'qwerty', \"updated_at\" = '2009-02-08' ;");
    
    $sql = $db->update('products', array('title' => 'qwerty', 'updated_at' => '2009-02-08'), array('id' => 123));
    $this->assert_equal($sql, "UPDATE \"products\" SET \"title\" = 'qwerty', \"updated_at\" = '2009-02-08' WHERE \"id\" = '123' ;");
    
    $sql = $db->update('products', array('title' => 'qwerty', 'updated_at' => '2009-02-08'), 'id = 456');
    $this->assert_equal($sql, "UPDATE \"products\" SET \"title\" = 'qwerty', \"updated_at\" = '2009-02-08' WHERE id = 456 ;");
    
    $sql = $db->update('products', array('title = :title, updated_at = :updated_at', array('title' => 'qwerty', 'updated_at' => '2009-02-08')), 'id = 456');
    $this->assert_equal($sql, "UPDATE \"products\" SET title = 'qwerty', updated_at = '2009-02-08' WHERE id = 456 ;");
    
    $data = array('title = :title', array('title' => 'qwerty'));
    
    $sql = $db->update('products', $data, 'category = 1', array('limit' => 10));
    $this->assert_equal($sql, "UPDATE \"products\" SET title = 'qwerty' WHERE category = 1 LIMIT 10 ;");
    
    $sql = $db->update('products', $data, 'category = 1', array('order' => 'id DESC'));
    $this->assert_equal($sql, "UPDATE \"products\" SET title = 'qwerty' WHERE category = 1 ORDER BY \"id\" DESC ;");
    
    $sql = $db->update('products', $data, 'category = 1', array('limit' => 5, 'order' => 'id ASC'));
    $this->assert_equal($sql, "UPDATE \"products\" SET title = 'qwerty' WHERE category = 1 ORDER BY \"id\" ASC LIMIT 5 ;");
    
    $sql = $db->update('products', $data, null, array('limit' => 5, 'order' => 'id ASC'));
    $this->assert_equal($sql, "UPDATE \"products\" SET title = 'qwerty' ORDER BY \"id\" ASC LIMIT 5 ;");
  }
  
  function test_delete()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->delete('products');
    $this->assert_equal($sql, "DELETE FROM \"products\" ;");
    
    $sql = $db->delete('products', array('id' => 123));
    $this->assert_equal($sql, "DELETE FROM \"products\" WHERE \"id\" = '123' ;");
    
    $sql = $db->delete('products', 'id = 123');
    $this->assert_equal($sql, "DELETE FROM \"products\" WHERE id = 123 ;");
    
    $sql = $db->delete('products', 'category = 1', array('limit' => 10));
    $this->assert_equal($sql, "DELETE FROM \"products\" WHERE category = 1 LIMIT 10 ;");
    
    $sql = $db->delete('products', 'category = 1', array('order' => 'id DESC'));
    $this->assert_equal($sql, "DELETE FROM \"products\" WHERE category = 1 ORDER BY \"id\" DESC ;");
    
    $sql = $db->delete('products', 'category = 1', array('limit' => 10, 'order' => 'id DESC'));
    $this->assert_equal($sql, "DELETE FROM \"products\" WHERE category = 1 ORDER BY \"id\" DESC LIMIT 10 ;");
    
    $sql = $db->delete('products', null, array('limit' => 10, 'order' => 'id ASC'));
    $this->assert_equal($sql, "DELETE FROM \"products\" ORDER BY \"id\" ASC LIMIT 10 ;");
  }
  
  function test_transactions()
  {
    $db = new FakeAdapter(array());
    
    $sql = $db->transaction('begin');
    $this->assert_equal($sql, "BEGIN ;");
    
    $sql = $db->transaction('BeGin');
    $this->assert_equal($sql, "BEGIN ;");
    
    $sql = $db->transaction('COMMIT');
    $this->assert_equal($sql, "COMMIT ;");
    
    $sql = $db->transaction('commit');
    $this->assert_equal($sql, "COMMIT ;");
    
    $sql = $db->transaction('rollback');
    $this->assert_equal($sql, "ROLLBACK ;");
  }
}

new Test_ConnectionAdapter_AbstractAdapter();

?>
