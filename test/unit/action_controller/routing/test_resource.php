<?php
require_once __DIR__.'/../../../unit.php';
use Misago\ActionController;

class Test_ActionController_Routing_Resource extends Misago\Unit\TestCase
{
  protected $fixtures = array();
  
  function test_map_resource()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_equal($map->route('GET', 'events'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'index',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'event/1'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'show',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'event/new'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'neo',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'event/1/edit'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'edit',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('POST', 'event'), array(
      ':method'     => 'POST',
      ':controller' => 'events',
      ':action'     => 'create',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('PUT', 'event/1'), array(
      ':method'     => 'PUT',
      ':controller' => 'events',
      ':action'     => 'update',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('DELETE', 'event/1'), array(
      ':method'     => 'DELETE',
      ':controller' => 'events',
      ':action'     => 'delete',
      ':id'         => '1',
      ':format'     => null,
    ));
    
    $this->assert_equal($map->route('GET', 'events/widget'), array(
      ':method'     => 'GET',
      ':controller' => 'events',
      ':action'     => 'widget',
      ':format'     => null,
    ));
  }
  
  function test_named_routes_for_resource()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_true(function_exists('accounts_path'));
    $this->assert_true(function_exists('show_account_path'));
    $this->assert_true(function_exists('new_account_path'));
    $this->assert_true(function_exists('create_account_path'));
    $this->assert_true(function_exists('edit_account_path'));
    $this->assert_true(function_exists('update_account_path'));
    $this->assert_true(function_exists('delete_account_path'));
    
    $this->assert_equal(accounts_path(), new ActionController\Routing\Path('GET', 'accounts'));
    $this->assert_equal(accounts_url(),  new ActionController\Routing\Url('GET', 'accounts'));
    
    $this->assert_equal(show_account_path(array(':id' => 1)), new ActionController\Routing\Path('GET', 'account/1'));
    $this->assert_equal(show_account_url(array(':id' => 1)),  new ActionController\Routing\Url('GET', 'account/1'));
    
    $this->assert_equal(new_account_path(), new ActionController\Routing\Path('GET', 'account/new'));
    $this->assert_equal(new_account_url(),  new ActionController\Routing\Url('GET', 'account/new'));
    
    $this->assert_equal(edit_account_path(array(':id' => 1)), new ActionController\Routing\Path('GET', 'account/1/edit'));
    $this->assert_equal(edit_account_url(array(':id' => 1)),  new ActionController\Routing\Url('GET', 'account/1/edit'));
    
    $this->assert_equal(create_account_path(), new ActionController\Routing\Path('POST', 'account'));
    $this->assert_equal(create_account_url(),  new ActionController\Routing\Url('POST', 'account'));
    
    $this->assert_equal(update_account_path(array(':id' => 1)), new ActionController\Routing\Path('PUT', 'account/1'));
    $this->assert_equal(update_account_url(array(':id' => 1)),  new ActionController\Routing\Url('PUT', 'account/1'));
    
    $this->assert_equal(delete_account_path(array(':id' => 1)), new ActionController\Routing\Path('DELETE', 'account/1'));
    $this->assert_equal(delete_account_url(array(':id' => 1)),  new ActionController\Routing\Url('DELETE', 'account/1'));
    
    $this->assert_equal(edit_account_path(45), new ActionController\Routing\Path('GET', 'account/45/edit'));
    $this->assert_equal(edit_account_url(45),  new ActionController\Routing\Url('GET', 'account/45/edit'));
    
    $this->assert_equal(show_account_path(72), new ActionController\Routing\Path('GET', 'account/72'));
    $this->assert_equal(show_account_url(72),  new ActionController\Routing\Url('GET', 'account/72'));
  }
  /*
  function test_nested_resources()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_equal((string)discussions_path(), '/discussions');
    $this->assert_equal(discussion_messages_path(array(':discussion_id' => 34)), new ActionController\Routing\Path('GET', 'discussions/34/messages'));
    $this->assert_equal(new_discussion_message_path(array(':discussion_id' => 43)), new ActionController\Routing\Path('GET', 'discussions/43/messages/new'));
    $this->assert_equal(create_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)), new ActionController\Routing\Path('POST', 'discussions/13/messages'));
    $this->assert_equal(show_discussion_message_path(array(':discussion_id' => 46, ':id' => 12)), new ActionController\Routing\Path('GET', 'discussions/46/messages/12'));
    $this->assert_equal(edit_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)), new ActionController\Routing\Path('GET', 'discussions/13/messages/26/edit'));
    $this->assert_equal(update_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)), new ActionController\Routing\Path('PUT', 'discussions/13/messages/26'));
    $this->assert_equal(delete_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)), new ActionController\Routing\Path('DELETE', 'discussions/13/messages/26'));
    
    $this->assert_equal((string)events_path(), '/event');
    $this->assert_equal(event_tickets_path(array(':event_id' => 12)), new ActionController\Routing\Path('GET', 'event/12/tickets'));
    $this->assert_equal(event_description_path(array(':event_id' => 12)), new ActionController\Routing\Path('GET', 'event/12/about'));
  }
  */
}

?>
