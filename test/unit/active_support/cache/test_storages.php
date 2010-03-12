<?php
require_once __DIR__.'/../../../unit.php';

abstract class Test_ActiveSupport_Cache_Store extends Test\Unit\TestCase
{
  function test_storage()
  {
    # cache is empty
    $this->assert_false($this->cache->read('var'));
    $this->assert_null($this->cache->fetch('var'));
    $this->assert_equal($this->cache->fetch('var', 0), 0);
    
    # caching first var (no ttl)
    $this->cache->write('var', 'value');
    $this->assert_equal($this->cache->read('var'), 'value');
  }
  
  function test_invoke()
  {
    $cache = $this->cache;
    $test1 = $cache('test_storages', function() { return 'a,b'; });
    $test2 = $cache('test_storages', function() { return 'a,b,c'; });
    $this->assert_equal($test2, $test1,
      "the first call must store 'a,b' and the second call must return it.");
  }
  
  function test_increment_decrement()
  {
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
  }
  
  function test_multiple()
  {
    $this->cache->write(array('var_a' => 1, 'var_b' => 2));
    $this->assert_equal($this->cache->read(array('var_b', 'var_a')), array('var_a' => 1, 'var_b' => 2));
    
    $this->assert_equal($this->cache->increment(array('var_a', 'var_b')), array('var_a' => 2, 'var_b' => 3));
    $this->assert_equal($this->cache->read(array('var_b', 'var_a')), array('var_a' => 2, 'var_b' => 3));
    
    $this->cache->increment(array('var_a', 'var_b'), 2);
    $this->assert_equal($this->cache->read(array('var_b', 'var_a')), array('var_a' => 4, 'var_b' => 5));
    
    $this->assert_equal($this->cache->decrement(array('var_a', 'var_b'), 3), array('var_a' => 1, 'var_b' => 2));
    $this->assert_equal($this->cache->read(array('var_b', 'var_a')), array('var_a' => 1, 'var_b' => 2));
    
    $this->cache->delete(array('var_a', 'var_b'));
    $this->assert_equal($this->cache->read(array('var_b', 'var_a')), array());
  }
  
  function test_write_once()
  {
    # FileStore doesn't support write_once() and APC doesn't return
    # false like it should.
    if (get_class($this) == 'Test_ActiveSupport_Cache_MemcacheStore'
      or get_class($this) == 'Test_ActiveSupport_Cache_RedisStore')
    {
      $this->assert_true($this->cache->write_once('uniq_token', 'foo'));
      $this->assert_false($this->cache->write_once('uniq_token', 'bar'));
      $this->assert_equal($this->cache->read('uniq_token'), 'foo');
    }
  }
  
  function test_expires_in()
  {
    # APC supports +expires_in+ but has a bug: http://pecl.php.net/bugs/bug.php?id=13331
    # Only Memcache & Redis support expires_in.
    if (get_class($this) == 'Test_ActiveSupport_Cache_MemcacheStore'
      or get_class($this) == 'Test_ActiveSupport_Cache_RedisStore')
    {
      # caching second var (with ttl)
      $this->cache->write('other_var', '23', array('expires_in' => 2));
      $this->assert_equal($this->cache->read('other_var'), '23');
      sleep(3);
      $this->assert_false($this->cache->read('other_var'));
      $this->assert_equal($this->cache->read('var'), 'value');
    }
  }
  
  function test_clear()
  {
    $this->cache->clear();
    $this->assert_false($this->cache->read('other_var'));
    $this->assert_false($this->cache->read('inc'));
  }
}

class Test_ActiveSupport_Cache_MemoryStore extends Test_ActiveSupport_Cache_Store
{
  function setup() {
    $this->cache = new Misago\ActiveSupport\Cache\MemoryStore();
  }
}

class Test_ActiveSupport_Cache_FileStore extends Test_ActiveSupport_Cache_Store
{
  function setup() {
    $this->cache = new Misago\ActiveSupport\Cache\FileStore();
  }
}

if (class_exists('\Memcache', false))
{
  class Test_ActiveSupport_Cache_MemcacheStore extends Test_ActiveSupport_Cache_Store
  {
    function setup() {
      $this->cache = new Misago\ActiveSupport\Cache\MemcacheStore();
    }
  }
  new Test_ActiveSupport_Cache_MemcacheStore();
}

class Test_ActiveSupport_Cache_RedisStore extends Test_ActiveSupport_Cache_Store
{
  function setup() {
    $this->cache = new Misago\ActiveSupport\Cache\RedisStore();
  }
}

?>
