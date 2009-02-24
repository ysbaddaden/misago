<?php
$_ENV['MISAGO_ENV'] = 'test';

require_once dirname(__FILE__)."/../test_app/config/boot.php";
require_once dirname(__FILE__)."/../../lib/unit/test_case.php";


class Test_Unit_TestCase extends Unit_TestCase
{
  function test_load_fixtures()
  {
    $db = ActiveRecord_Connection::get($_ENV['MISAGO_ENV']);
    
    $data = $db->select_values('select id from products order by id asc;');
    $this->assert_equal("products must be empty", $data, array());
    
    $this->fixtures("products");
    
    $data = $db->select_values('select id from products order by id asc limit 3;');
    $this->assert_equal("products must have been populated", $data, array('1', '2', '3'));
    
    $data = $db->select_values('select id from invoices order by id asc ;');
    $this->assert_equal("invoices must be empty", $data, array());
    
    $data = $db->select_values('select id from orders order by id asc ;');
    $this->assert_equal("orders must be empty", $data, array());
    
    $this->fixtures("orders, invoices");
    
    $data = $db->select_values('select id from invoices order by id asc limit 2;');
    $this->assert_equal("orders must have been populated", $data, array('1', '2'));
    
    $data = $db->select_values('select id from orders order by id asc limit 3;');
    $this->assert_equal("invoices must have been populated", $data, array('1', '2', '3'));
  }
}

new Test_Unit_TestCase();

?>
