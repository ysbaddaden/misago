<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__)."/../../../test_app/config/boot.php";

class Test_ActiveSupport_Cache_Store extends Unit_Test
{
  function test_storage()
  {
    $this->setup();
    
    # cache is empty
    $this->assert_false($this->cache->read('var'));
    $this->assert_null($this->cache->fetch('var'));
    $this->assert_equal($this->cache->fetch('var', 0), 0);
    
    # caching first var (no ttl)
    $this->cache->write('var', 'value');
    $this->assert_equal($this->cache->read('var'), 'value');
    
    # APC has a bug: http://pecl.php.net/bugs/bug.php?id=13331
    if (get_class($this) != 'Test_ActiveSupport_Cache_ApcStore')
    {
      # caching second var (with ttl)
      $this->cache->write('other_var', '23', array('expires_in' => 2));
      $this->assert_equal($this->cache->read('other_var'), '23');
      sleep(3);
      $this->assert_false($this->cache->read('other_var'));
      $this->assert_equal($this->cache->read('var'), 'value');
    }
    
    # increment/decrement (unknown vars)
    $this->assert_equal($this->cache->increment('inc'), 1);
    $this->assert_equal($this->cache->decrement('dec'), 0);
    $this->assert_equal((int)$this->cache->read('inc'), 1);
    $this->assert_equal((int)$this->cache->read('dec'), 0);
    
    # increment/decrement (vars have been created)
    $this->assert_equal($this->cache->increment('inc'), 2);
    $this->assert_equal($this->cache->decrement('inc'), 1);
    $this->assert_equal($this->cache->increment('inc', 2), 3);
    $this->assert_equal($this->cache->decrement('inc', 3), 0);
    
    # cache clear
    $this->cache->clear();
    $this->assert_false($this->cache->read('other_var'));
    $this->assert_false($this->cache->read('inc'));
  }
}

class Test_ActiveSupport_Cache_MemoryStore extends Test_ActiveSupport_Cache_Store
{
  function setup()
  {
    $this->cache = new ActiveSupport_Cache_MemoryStore();
  }
}
new Test_ActiveSupport_Cache_MemoryStore();

class Test_ActiveSupport_Cache_FileStore extends Test_ActiveSupport_Cache_Store
{
  function setup()
  {
    $this->cache = new ActiveSupport_Cache_FileStore();
  }
}
new Test_ActiveSupport_Cache_FileStore();

class Test_ActiveSupport_Cache_ApcStore extends Test_ActiveSupport_Cache_Store
{
  function setup()
  {
    $this->cache = new ActiveSupport_Cache_ApcStore();
  }
}
new Test_ActiveSupport_Cache_ApcStore();

class Test_ActiveSupport_Cache_MemcacheStore extends Test_ActiveSupport_Cache_Store
{
  function setup()
  {
    $this->cache = new ActiveSupport_Cache_MemcacheStore();
  }
}
new Test_ActiveSupport_Cache_MemcacheStore();

?>
