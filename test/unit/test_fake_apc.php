<?php
require_once __DIR__.'/../unit.php';

class TestFakeApc extends Test\Unit\TestCase
{
  function test_apc_store()
  {
    $this->assert_true(apc_store('foo', 'BAR'));
    $this->assert_equal(apc_fetch('foo'), 'BAR');
    
    apc_store('foo', 'FOO');
    $this->assert_equal(apc_fetch('foo'), 'FOO');
  }
  
  function test_apc_add()
  {
    apc_add('foofoo', 'BAR');
    apc_add('foofoo', 'FOO');
    $this->assert_equal(apc_fetch('foofoo'), 'BAR');
  }
  
  function test_apc_delete()
  {
    apc_delete('foofoo');
    $this->assert_false(apc_fetch('foofoo'));
  }
  
  function apc_clear_cache()
  {
    $this->assert_equal(apc_fetch('foo'), 'bar');
    apc_clear_cache();
    $this->assert_false(apc_fetch('foo'));
  }
}

?>
