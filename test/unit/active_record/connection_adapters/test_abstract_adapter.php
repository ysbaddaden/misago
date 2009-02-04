<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['environment'] = 'test';

require_once "$location/lib/unit_test.php";
require_once "$location/lib/active_support/array.php";
require_once "$location/lib/active_record/connection_adapters/abstract_adapter.php";

class FakeAdapter extends ActiveRecord_ConnectionAdapters_AbstractAdapter
{
  function execute($sql)
  {
    return preg_replace('/\s{2,}/', ' ', $sql);
  }
  
  function connect() {}
  function disconnect() {}
  function select_rows($sql) {}
  function columns($sql) {}
  function is_active() {}
}

class Test_DBO_BaseDriver extends Unit_Test
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

new Test_DBO_BaseDriver();

?>
