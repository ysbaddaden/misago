<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['environment'] = 'test';

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
  function & select_rows($sql) {}
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
  
  function test_insert()
  {
    $db = new FakeAdapter(array());
    $sql = $db->insert('products', array('title' => 'azerty', 'created_at' => '2009-02-06'));
    $this->assert_equal("", $sql, "INSERT INTO \"products\" ( \"title\", \"created_at\" ) VALUES ( 'azerty', '2009-02-06' ) ;");
  }
  
  /*
  function test_quote_columns()
  {
    $db = new FakeDriver(array());
    
    $fields = $db->fields("a, b, c");
    $this->assert_equal("simple list", $fields, '"a", "b", "c"');

    $fields = $db->fields("a.b, c.d");
    $this->assert_equal("list of table.field", $fields, '"a"."b", "c"."d"');
    
    $fields = $db->fields("a.b.c, d.e.f");
    $this->assert_equal("list of schema.table.field", $fields, '"a"."b"."c", "d"."e"."f"');
    
    $fields = $db->fields("*, a.*");
    $this->assert_equal("do not escape widlcards", $fields, '*, "a".*');

    $fields = $db->fields("-! a, b, c");
    $this->assert_equal("no param shall be parsed", $fields, 'a, b, c');

    $fields = $db->fields("a, -! b, c");
    $this->assert_equal("one param shall not be parsed", $fields, '"a", b, "c"');

    $fields = $db->fields(" -! a, b, c");
    $this->assert_equal("edge case, first param musn't be parsed", $fields, 'a, "b", "c"');

    $field = $db->field("COUNT(b)");
    $this->assert_equal("do not parse function", $field, 'COUNT("b")');

    $field = $db->field("COUNT(*)");
    $this->assert_equal("do not parse function + wildcard", $field, 'COUNT(*)');
    
    $test = $db->field("DATE_ADD(a, b)");
    $this->assert_equal("correctly handle multiple fields in function", $test, 'DATE_ADD("a", "b")');
    
#    $test = $db->field("DATE_ADD(a, DATE_SUB(b, c))");
#    $this->assert_equal("recursive parsing of functions", $test, 'DATE_ADD("a", DATE_SUB("b", "c"))');
  }
*/
/*
  function test_values()
  {
    $db = new FakeDriver(array());
    
    $value = $db->value("sendmail's report");
    $this->assert_equal("simple string escape", $value, "'sendmail\\'s report'");
    
    $value = $db->value("-! 'sendmail\\'s report'");
    $this->assert_equal("do not escape string", $value, "'sendmail\\'s report'");

    $values = $db->values(array('a', 'b', 'c'));
    $this->assert_equal("escape a list of values from array", $values, "'a', 'b', 'c'");

    $values = $db->values('a, b, c');
    $this->assert_equal("escape a list of values from string", $values, "'a', 'b', 'c'");

    $values = $db->values(array('a', '-! b', 'c'));
    $this->assert_equal("escape a list of values but one", $values, "'a', b, 'c'");

    $values = $db->values('a, b, -! c');
    $this->assert_equal("escape a list of values but one from string", $values, "'a', 'b', c");

    $values = $db->values(' -! a, b, c');
    $this->assert_equal("escape a list of values but the first", $values, "a, 'b', 'c'");
  }
*/
}

new Test_ConnectionAdapter_AbstractAdapter();

?>
