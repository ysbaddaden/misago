<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once dirname(__FILE__)."/../../test_app/config/boot.php";
require_once dirname(__FILE__)."/../../../lib/unit/test_case.php";


class Test_Unit_TestCase extends Unit_TestCase
{
  protected $fixtures = array();
  
  function test_load_fixtures()
  {
    $db = ActiveRecord_Connection::get($_SERVER['MISAGO_ENV']);
    
    $data = $db->select_values('select id from products order by id asc;');
    $this->assert_equal($data, array(), "products must be empty");
    
    $this->fixtures("products");
    
    $data = $db->select_values('select id from products order by id asc limit 3;');
    $this->assert_equal($data, array('1', '2', '3'), "products must have been populated");
    
    $data = $db->select_values('select id from invoices order by id asc ;');
    $this->assert_equal($data, array(), "invoices must be empty");
    
    $data = $db->select_values('select id from orders order by id asc ;');
    $this->assert_equal($data, array(), "orders must be empty");
    
    $this->fixtures('orders', 'invoices');
    
    $data = $db->select_values('select id from invoices order by id asc limit 2;');
    $this->assert_equal($data, array('1', '2'), "orders must have been populated");
    
    $data = $db->select_values('select id from orders order by id asc limit 3;');
    $this->assert_equal($data, array('1', '2', '3'), "invoices must have been populated");
  }
}

new Test_Unit_TestCase();

?>
