<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['environment'] = 'test';

require_once "$location/lib/unit_test.php";
require_once "$location/lib/active_support/array.php";
require_once "$location/lib/active_record/drivers/base.php";

class FakeDriver extends DBO_Base
{
  function execute($sql)
  {
    return $sql;
  }
  function parse_results($results, $scope) { }
}

class Test_DBO_BaseDriver extends Unit_Test
{
  function test_fields()
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

  function test_conditions()
  {
    $db = new FakeDriver(array());
    
    $test = $db->conditions("");
    $this->assert_equal("empty string", $test, "");
    
    $test = $db->conditions(array());
    $this->assert_equal("empty array", $test, "");
    
    $test = $db->conditions("a = 'b' AND c = 'd'");
    $this->assert_equal("unparsed string", $test, "WHERE a = 'b' AND c = 'd'");
    
    $test = $db->conditions(array("a" => "b", "c" => "d"));
    $this->assert_equal("array of equals", $test, "WHERE \"a\" = 'b' AND \"c\" = 'd'");
    
    $test = $db->conditions(array("a" => "> b", "c" => "< d", "e" => "<> f", "g" => "LIKE %toto%"));
    $this->assert_equal("array with operators", $test,
      "WHERE \"a\" > 'b' AND \"c\" < 'd' AND \"e\" <> 'f' AND \"g\" LIKE '%toto%'");
    
    $test = $db->conditions(array("a" => "BETWEEN b AND c"));
    $this->assert_equal("array with between operator", $test, "WHERE \"a\" BETWEEN 'b' AND 'c'");
    
    $test = $db->conditions(array("a" => array(1, 2, 3)));
    $this->assert_equal("IN (list of values)", $test, "WHERE \"a\" IN ('1', '2', '3')");
    
    $test = $db->conditions(array("a = ? AND c = ?", array('b', 'd')));
    $this->assert_equal("safe mode with question marks", $test, "WHERE a = 'b' AND c = 'd'");
    
    $test = $db->conditions(array("abcd = :abcd AND efg = :efg",
      array('abcd' => 'azerty', 'efg' => 'qwerty')));
    $this->assert_equal("safe mode with symbols", $test, "WHERE abcd = 'azerty' AND efg = 'qwerty'");
    
    $test = $db->conditions(array("a = :a AND b = :b", array('a' => ':b', 'b' => 'c')));
    $this->assert_equal("safe mode with symbols shouldn't recursively replace symbols",
      $test, "WHERE a = ':b' AND b = 'c'");
    
    $test = $db->conditions(array("a = ? AND b = ?", array('toto ?', 'c')));
    $this->assert_equal("safe mode with question marks shouldn't recursively replace question marks",
      $test, "WHERE a = 'toto ?' AND b = 'c'");
  }
  
  function test_insert()
  {
    $db  = new FakeDriver(array());
    
    $sql = $db->insert('books', array(
      'title'       => "Sendmail's reports",
      'description' => "bla bla"
    ));
    $this->assert_equal("single quote in value", $sql, "INSERT INTO books (\"title\", \"description\") VALUES ('Sendmail\'s reports', 'bla bla') ;");
    
    $sql = $db->insert('books', array(
      'title'       => "test test",
      'description' => "this is a \"test test\""
    ));
    $this->assert_equal("quote in value", $sql, "INSERT INTO books (\"title\", \"description\") VALUES ('test test', 'this is a \\\"test test\\\"') ;");
  }
}

new Test_DBO_BaseDriver();

?>
