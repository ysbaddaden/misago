<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../test_app/config/boot.php';

class TestCSRFProtection extends Misago\ActionController\TestCase
{
  
}
new TestCSRFProtection();

?>
