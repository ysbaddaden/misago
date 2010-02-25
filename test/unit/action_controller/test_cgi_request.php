<?php
require_once __DIR__.'/../../unit.php';
use Misago\ActionController;

class Test_ActionController_CgiRequest extends Misago\Unit\TestCase
{
  function test_host_and_port()
  {
    $BACKUP = $_SERVER;
    
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI']    = 'http://www.example.com/';
    $r = new ActionController\CgiRequest();
    
    $_SERVER['HTTP_HOST'] = 'www.example.com';
    $this->assert_equal($r->host(), 'www.example.com');
    $this->assert_equal($r->port(), 80);
    
    $_SERVER['HTTP_HOST'] = 'localhost:123';
    $this->assert_equal($r->host(), 'localhost');
    $this->assert_equal($r->port(), 123);
    
    $_SERVER['HTTP_HOST'] = '127.0.0.1:443';
    $this->assert_equal($r->host(), '127.0.0.1');
    $this->assert_equal($r->port(), 443);
    
    $_SERVER['HTTP_HOST'] = '[::1]:3000';
    $this->assert_equal($r->host(), '[::1]');
    $this->assert_equal($r->port(), 3000);
    
    $_SERVER['HTTP_HOST'] = '[2a01::1]';
    $this->assert_equal($r->host(), '[2a01::1]');
    $this->assert_equal($r->port(), 80);
    
    $_SERVER['HTTPS'] = '1';
    $_SERVER['HTTP_HOST'] = '[2a01:3e4::1]';
    $this->assert_equal($r->host(), '[2a01:3e4::1]');
    $this->assert_equal($r->port(), 443);
    
    $_SERVER = $BACKUP;
  }
}

?>
