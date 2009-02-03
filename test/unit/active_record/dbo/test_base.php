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
    return preg_replace('/\s{2,}/', ' ', $sql);
  }
  
  function parse_results($results, $scope)
  {
    return $results;
  }
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
  
  function test_order()
  {
    $db = new FakeDriver(array());
    
    $test = $db->order(null);
    $this->assert_equal("no order", $test, "");
    
    $test = $db->order('a');
    $this->assert_equal("simple order", $test, "ORDER BY \"a\"");
    
    $test = $db->order('a,b');
    $this->assert_equal("multiple fields", $test, "ORDER BY \"a\", \"b\"");
    
    $test = $db->order('a DESC');
    $this->assert_equal("order with way", $test, "ORDER BY \"a\" DESC");
    
    $test = $db->order('a NULLS LAST, b, c ASC');
    $this->assert_equal("by multiple fields", $test, "ORDER BY \"a\" NULLS LAST, \"b\", \"c\" ASC");
  }
    
  function test_group()
  {
    $db = new FakeDriver(array());
    
    $test = $db->group(null);
    $this->assert_equal("no group", $test, "");
    
    $test = $db->group('a');
    $this->assert_equal("simple group", $test, "GROUP BY \"a\"");
    
    $test = $db->group('a,b');
    $this->assert_equal("multiple fields", $test, "GROUP BY \"a\", \"b\"");
    
    $test = $db->group('a DESC');
    $this->assert_equal("group with way", $test, "GROUP BY \"a\" DESC");
    
    $test = $db->group('a NULLS LAST, b, c ASC');
    $this->assert_equal("by multiple fields", $test, "GROUP BY \"a\" NULLS LAST, \"b\", \"c\" ASC");
  }
  
  function test_limit()
  {
    $db = new FakeDriver(array());
    
    $test = $db->limit(null);
    $this->assert_equal("no limit", $test, "");
    
    $test = $db->limit(10);
    $this->assert_equal("limit", $test, "LIMIT 10");
    
    $test = $db->limit(10, 15);
    $this->assert_equal("limit with offset", $test, "LIMIT 10 OFFSET 140");
  }
  
  function test_insert()
  {
    $db  = new FakeDriver(array());
    
    $sql = $db->insert('books', array(
      'title'       => "Sendmail's reports",
      'description' => "bla bla"
    ));
    $this->assert_equal("single quote in value", $sql, "INSERT INTO \"books\" (\"title\", \"description\") VALUES ('Sendmail\'s reports', 'bla bla') ;");
    
    $sql = $db->insert('books', array(
      'title'       => "test test",
      'description' => "this is a \"test test\""
    ));
    $this->assert_equal("quote in value", $sql, "INSERT INTO \"books\" (\"title\", \"description\") VALUES ('test test', 'this is a \\\"test test\\\"') ;");
  }
  
  function test_update()
  {
    $db  = new FakeDriver(array());
    
    $data  = array('a' => 'b', 'c' => 'd');
    $sql = $db->update('books', $data);
    $this->assert_equal("without conditions", $sql, "UPDATE \"books\" SET \"a\" = 'b', \"c\" = 'd' ;");
    
    $data       = array('a' => 'b', 'c' => 'd');
    $conditions = array('e' => 'f');
    $sql = $db->update('books', $data, $conditions);
    $this->assert_equal("with conditions", $sql, "UPDATE \"books\" SET \"a\" = 'b', \"c\" = 'd' WHERE \"e\" = 'f' ;");
  }
  
  function test_delete()
  {
    $db  = new FakeDriver(array());
    
    $sql = $db->delete('books');
    $this->assert_equal("without conditions", $sql, "DELETE FROM \"books\" ;");
    
    $conditions = array('e' => 'f');
    $sql = $db->delete('books', $conditions);
    $this->assert_equal("with conditions", $sql, "DELETE FROM \"books\" WHERE \"e\" = 'f' ;");
  }
  
  function test_select()
  {
    $db  = new FakeDriver(array());
    
    $sql = $db->select('books');
    $this->assert_equal("basic", $sql, "SELECT * FROM \"books\" ;");
    
    $sql = $db->select('books', "title,description");
    $this->assert_equal("with fields", $sql, "SELECT \"title\", \"description\" FROM \"books\" ;");
    
    $sql = $db->select('books', array(
      'conditions' => array('name' => 'LIKE %toto%')
    ));
    $this->assert_equal("with conditions", $sql, "SELECT * FROM \"books\" WHERE \"name\" LIKE '%toto%' ;");
    
    $sql = $db->select('books', array(
      'fields'     => 'title,description',
      'conditions' => array('name' => 'LIKE %toto%')
    ));
    $this->assert_equal("with fields and conditions", $sql,
      "SELECT \"title\", \"description\" FROM \"books\" WHERE \"name\" LIKE '%toto%' ;");
    
    $sql = $db->select('books', array(
      'conditions' => array('name' => 'LIKE %toto%'),
      'limit' => 10,
    ));
    $this->assert_equal("with limit", $sql,
      "SELECT * FROM \"books\" WHERE \"name\" LIKE '%toto%' LIMIT 10 ;");
    
    $sql = $db->select('books', array(
      'conditions' => array('name' => 'LIKE %toto%'),
      'limit' => 10, 'page' => 20,
    ));
    $this->assert_equal("with pagination", $sql,
      "SELECT * FROM \"books\" WHERE \"name\" LIKE '%toto%' LIMIT 10 OFFSET 190 ;");
    
    $sql = $db->select('books', array(
      'conditions' => array('name' => 'LIKE %toto%'),
      'limit' => 10, 'page' => 20,
      'order' => "created_at DESC",
    ));
    $this->assert_equal("with orderby", $sql,
      "SELECT * FROM \"books\" WHERE \"name\" LIKE '%toto%' ORDER BY \"created_at\" DESC LIMIT 10 OFFSET 190 ;");
    
    $sql = $db->select('books', array(
      'fields' => "COUNT(*)",
      'group'  => "created_at ASC",
    ));
    $this->assert_equal("with groupby", $sql,
      "SELECT COUNT(*) FROM \"books\" GROUP BY \"created_at\" ASC ;");
  }
}

new Test_DBO_BaseDriver();

?>
