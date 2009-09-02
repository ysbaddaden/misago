<?php

$location = dirname(__FILE__).'/../../..';
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}

require_once "$location/test/test_app/config/boot.php";

class Test_ActionController_Flash extends Unit_Test
{
  function test_hash()
  {
    $flash = new ActionController_Flash();
    $flash['notice']  = 'some message';
    $flash['message'] = array('toto' => 'data');
    
    $this->assert_true('notice was set', isset($flash['notice']));
    $this->assert_true('message was set', isset($flash['message']));
    $this->assert_equal('must return notice value', $flash['notice'],  'some message');
    $this->assert_equal('value can be whatever', $flash['message'], array('toto' => 'data'));
    
    unset($flash['notice']);
    $this->assert_false('notice was removed from hash', isset($flash['notice']));
    $this->assert_true('message is still available', isset($flash['message']));
    
    $flash->discard();
  }
  
  function test_data_persistence()
  {
    $flash = new ActionController_Flash();
    $flash['notice'] = 'my message';
    $flash['ary'] = array('hash');
    
    unset($flash);
    
    $flash = new ActionController_Flash();
    $this->assert_true('data persisted to the next query', isset($flash['notice']));
    $this->assert_equal("data musn't have been modified", $flash['notice'], 'my message');
    $this->assert_equal("you may pass anything remember?", $flash['ary'], array('hash'));
    
    $flash->discard();
  }
  
  function test_limited_persistence()
  {
    $flash = new ActionController_Flash();
    $flash['notice'] = 'my message';
    unset($flash);
    
    $flash = new ActionController_Flash();
    $flash['message'] = 'another message';
    $this->assert_true("data persisted to this 2nd request", isset($flash['notice']));
    unset($flash);
    
    $flash = new ActionController_Flash();
    $this->assert_false("data persistance is limited to the next query only", isset($flash['notice']));
    $this->assert_true("but I must be able to access the data from the previous request", isset($flash['message']));
    
    $flash->discard();
  }
  
  function test_persistence_when_key_is_rewritten()
  {
    $flash = new ActionController_Flash();
    $flash['notice'] = 'my message';
    unset($flash);
    
    $flash = new ActionController_Flash();
    $flash['notice'] = 'my message';
    unset($flash);
    
    $flash = new ActionController_Flash();
    $this->assert_true("data persisted to this 3rd request!", isset($flash['notice']));
    
    $flash->discard();
  }
  
  function test_discard()
  {
    $flash = new ActionController_Flash();
    $flash['notice']  = 'aaa';
    $flash['error']   = 'bbb';
    $flash['message'] = 'ccc';
    $this->assert_true('notice was set', isset($flash));

    $flash->discard('notice');
    $this->assert_false('notice was discarded', isset($flash['notice']));
    
    unset($flash);
    $flash = new ActionController_Flash();
    $this->assert_false('notice is still discarded', isset($flash['notice']));
    $this->assert_equal('there are 2 variables left', $flash->count(), 2);
    
    $flash['variable'] = "let's add a third variable";
    $flash->discard();
    $this->assert_equal('there are no variables left', $flash->count(), 0);
    
    unset($flash);
    $flash = new ActionController_Flash();
    $this->assert_equal('there are still no variables left', $flash->count(), 0);
  }
}

@session_start();
new Test_ActionController_Flash();
@session_destroy();

?>
