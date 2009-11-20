<?php
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'test';
}
require_once dirname(__FILE__).'/../../../test/test_app/config/boot.php';
use Misago\ActionController;

class Test_ActionController_Flash extends Misago\Unit\Test
{
  function test_hash()
  {
    $flash = new ActionController\Flash();
    $flash['notice']  = 'some message';
    $flash['message'] = array('toto' => 'data');
    
    $this->assert_true(isset($flash['notice']));
    $this->assert_true(isset($flash['message']));
    $this->assert_equal($flash['notice'],  'some message', 'returns notice value');
    $this->assert_equal($flash['message'], array('toto' => 'data'), 'value can be whatever');
    
    unset($flash['notice']);
    $this->assert_false(isset($flash['notice']), 'notice was removed from hash');
    $this->assert_true(isset($flash['message']), 'message is still available');
    
    $flash->discard();
  }
  
  function test_data_persistence()
  {
    $flash = new ActionController\Flash();
    $flash['notice'] = 'my message';
    $flash['ary'] = array('hash');
    
    unset($flash);
    
    $flash = new ActionController\Flash();
    $this->assert_true(isset($flash['notice']), 'data persisted to the next query');
    $this->assert_equal($flash['notice'], 'my message', "data musn't have been modified");
    $this->assert_equal($flash['ary'], array('hash'), "you may pass anything, remember?");
    
    $flash->discard();
  }
  
  function test_limited_persistence()
  {
    $flash = new ActionController\Flash();
    $flash['notice'] = 'my message';
    unset($flash);
    
    $flash = new ActionController\Flash();
    $flash['message'] = 'another message';
    $this->assert_true(isset($flash['notice']), "data persisted to this 2nd request");
    unset($flash);
    
    $flash = new ActionController\Flash();
    $this->assert_false(isset($flash['notice']), "data persistance is limited to the next query only");
    $this->assert_true(isset($flash['message']), "but I must be able to access the data from the previous request");
    
    $flash->discard();
  }
  
  function test_persistence_when_key_is_rewritten()
  {
    $flash = new ActionController\Flash();
    $flash['notice'] = 'my message';
    unset($flash);
    
    $flash = new ActionController\Flash();
    $flash['notice'] = 'my message';
    unset($flash);
    
    $flash = new ActionController\Flash();
    $this->assert_true(isset($flash['notice']), "data persisted to this 3rd request!");
    
    $flash->discard();
  }
  
  function test_discard()
  {
    $flash = new ActionController\Flash();
    $flash['notice']  = 'aaa';
    $flash['error']   = 'bbb';
    $flash['message'] = 'ccc';
    $this->assert_true(isset($flash));

    $flash->discard('notice');
    $this->assert_false(isset($flash['notice']), 'notice was discarded');
    
    unset($flash);
    $flash = new ActionController\Flash();
    $this->assert_false(isset($flash['notice']), 'notice is still discarded');
    $this->assert_equal($flash->count(), 2, 'there are 2 variables left');
    
    $flash['variable'] = "let's add a third variable";
    $flash->discard();
    $this->assert_equal($flash->count(), 0, 'there are no variables left');
    
    unset($flash);
    $flash = new ActionController\Flash();
    $this->assert_equal($flash->count(), 0, 'there are still no variables left');
  }
}

@session_start();
new Test_ActionController_Flash();
@session_destroy();

?>
