<?php
require_once __DIR__.'/../../../unit.php';
use Misago\ActionController;

class Test_ActionController_Routing_Resource extends Misago\Unit\TestCase
{
  protected $fixtures = array();
  
  function test_map_resources()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_equal($map->route('GET', 'events'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'index', ':format' => null));
    
    $this->assert_equal($map->route('GET', 'events.json'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'index', ':format' => 'json'));
    
    $this->assert_equal($map->route('GET', 'events/new'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'neo', ':format' => null));
    
    $this->assert_equal($map->route('POST', 'events'), array(
      ':method' => 'POST', ':controller' => 'events', ':action' => 'create', ':format' => null));
    
    $this->assert_equal($map->route('POST', 'events.xml'), array(
      ':method' => 'POST', ':controller' => 'events', ':action' => 'create', ':format' => 'xml'));
    
    $this->assert_equal($map->route('GET', 'events/1'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'show', ':id' => '1', ':format' => null));
    
    $this->assert_equal($map->route('GET', 'events/1.xml'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'show', ':id' => '1', ':format' => 'xml'));
    
    $this->assert_equal($map->route('GET', 'events/1/edit'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'edit', ':id' => '1', ':format' => null));
    
    $this->assert_equal($map->route('PUT', 'events/1'), array(
      ':method' => 'PUT', ':controller' => 'events', ':action' => 'update', ':id' => '1', ':format' => null));
    
    $this->assert_equal($map->route('DELETE', 'events/1'), array(
      ':method' => 'DELETE', ':controller' => 'events', ':action' => 'delete', ':id' => '1', ':format' => null));
    
    $this->assert_equal($map->route('DELETE', 'events/1.json'), array(
      ':method' => 'DELETE', ':controller' => 'events', ':action' => 'delete', ':id' => '1', ':format' => 'json'));

    # collection
    $this->assert_equal($map->route('GET', 'events/archives'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'archives', ':format' => null));

    $this->assert_equal($map->route('GET', 'events/latest.rss'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'latest', ':format' => 'rss'));

    # member
    $this->assert_equal($map->route('PUT', 'events/1/publish'), array(
      ':method' => 'PUT', ':controller' => 'events', ':action' => 'publish', ':id' => 1, ':format' => null));
    
    $this->assert_equal($map->route('GET', 'events/1/tags.xml'), array(
      ':method' => 'GET', ':controller' => 'events', ':action' => 'tags', ':id' => 1, ':format' => 'xml'));
    
    $this->assert_not_equal($map->route('PUT', 'events/1/tags.xml'), array(
      ':method' => 'PUT', ':controller' => 'events', ':action' => 'tags', ':id' => 1, ':format' => 'xml'));
    
    # as
    $this->assert_equal($map->route('POST', 'kategorien'), array(
      ':method' => 'POST', ':controller' => 'categories', ':action' => 'create', ':format' => null));
    
    $this->assert_equal($map->route('PUT', 'kategorien/1.json'), array(
      ':method' => 'PUT', ':controller' => 'categories', ':action' => 'update', ':id' => 1, ':format' => 'json'));
    
    # only
    $this->assert_not_equal($map->route('GET', 'kategorien'), array(
      ':method' => 'GET', ':controller' => 'categories', ':action' => 'index', ':format' => null));
    
    # except
    $this->assert_not_equal($map->route('DELETE', 'kategorien/2'), array(
      ':method' => 'DELETE', ':controller' => 'categories', ':action' => 'delete', ':id' => 2, ':format' => null));
    
    # controller
    $this->assert_equal($map->route('GET', 'pictures'), array(
      ':method' => 'GET', ':controller' => 'images', ':action' => 'index', ':format' => null,
    ));
  }
  
  function test_map_resource()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_equal($map->route('GET', 'geocoder/new'), array(
      ':method' => 'GET', ':controller' => 'geocoder', ':action' => 'neo', ':format' => null));
    
    $this->assert_equal($map->route('GET', 'geocoder/new.xml'), array(
      ':method' => 'GET', ':controller' => 'geocoder', ':action' => 'neo', ':format' => 'xml'));
    
    $this->assert_equal($map->route('POST', 'geocoder'), array(
      ':method' => 'POST', ':controller' => 'geocoder', ':action' => 'create', ':format' => null));
    
    $this->assert_equal($map->route('GET', 'geocoder'), array(
      ':method' => 'GET', ':controller' => 'geocoder', ':action' => 'show', ':format' => null));
    
    $this->assert_equal($map->route('GET', 'geocoder.rss'), array(
      ':method' => 'GET', ':controller' => 'geocoder', ':action' => 'show', ':format' => 'rss'));
    
    $this->assert_equal($map->route('GET', 'geocoder/edit'), array(
      ':method' => 'GET', ':controller' => 'geocoder', ':action' => 'edit', ':format' => null));
    
    $this->assert_equal($map->route('PUT', 'geocoder'), array(
      ':method' => 'PUT', ':controller' => 'geocoder', ':action' => 'update', ':format' => null));
    
    $this->assert_equal($map->route('DELETE', 'geocoder'), array(
      ':method' => 'DELETE', ':controller' => 'geocoder', ':action' => 'delete', ':format' => null));
    
    # member
    $this->assert_equal($map->route('GET', 'geocoder/add'), array(
      ':method' => 'GET', ':controller' => 'geocoder', ':action' => 'add', ':format' => null));
    
    $this->assert_equal($map->route('DELETE', 'geocoder/remove.xml'), array(
      ':method' => 'DELETE', ':controller' => 'geocoder', ':action' => 'remove', ':format' => 'xml'));
    
    $this->assert_equal($map->route('GET', 'geocoder/all'), array(
      ':method' => 'GET', ':controller' => 'geocoder', ':action' => 'all', ':format' => null));
    
    # member + any
    $this->assert_equal($map->route('GET', 'geocoder/position'), array(
      ':method' => 'GET', ':controller' => 'geocoder', ':action' => 'position', ':format' => null));
    $this->assert_equal($map->route('PUT', 'geocoder/position'), array(
      ':method' => 'PUT', ':controller' => 'geocoder', ':action' => 'position', ':format' => null));
    
    # as
    $this->assert_equal($map->route('GET', 'profil'), array(
      ':method' => 'GET', ':controller' => 'profiles', ':action' => 'show', ':format' => null));
    
    $this->assert_equal($map->route('PUT', 'profil.xml'), array(
      ':method' => 'PUT', ':controller' => 'profiles', ':action' => 'update', ':format' => 'xml'));
    
    # only
    $this->assert_not_equal($map->route('GET', 'profil/new'), array(
      ':method' => 'GET', ':controller' => 'profiles', ':action' => 'neo', ':format' => null));
    
    # except
    $this->assert_not_equal($map->route('DELETE', 'profil'), array(
      ':method' => 'DELETE', ':controller' => 'profiles', ':action' => 'delete', ':format' => null));
  }
  
  function test_named_routes_for_resources()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_true(function_exists('accounts_path'));
    $this->assert_true(function_exists('account_path'));
    $this->assert_true(function_exists('new_account_path'));
    $this->assert_true(function_exists('edit_account_path'));
    
    $this->assert_true(function_exists('accounts_url'));
    $this->assert_true(function_exists('account_url'));
    $this->assert_true(function_exists('new_account_url'));
    $this->assert_true(function_exists('edit_account_url'));
    
    $this->assert_equal(accounts_path(), new ActionController\Routing\Path('GET', 'accounts'));
    $this->assert_equal(accounts_url(),  new ActionController\Routing\Url('GET', 'accounts'));
    
    $this->assert_equal(account_path(array(':id' => 1)), new ActionController\Routing\Path('GET', 'accounts/1'));
    $this->assert_equal(account_url(array(':id' => 1)),  new ActionController\Routing\Url('GET', 'accounts/1'));
    
    $this->assert_equal(new_account_path(), new ActionController\Routing\Path('GET', 'accounts/new'));
    $this->assert_equal(new_account_url(),  new ActionController\Routing\Url('GET', 'accounts/new'));
    
    $this->assert_equal(edit_account_path(array(':id' => 1)), new ActionController\Routing\Path('GET', 'accounts/1/edit'));
    $this->assert_equal(edit_account_url(array(':id' => 1)),  new ActionController\Routing\Url('GET', 'accounts/1/edit'));
    
    $this->assert_equal(edit_account_path(45), new ActionController\Routing\Path('GET', 'accounts/45/edit'));
    $this->assert_equal(edit_account_url(45),  new ActionController\Routing\Url('GET', 'accounts/45/edit'));
    
    $this->assert_equal(account_path(72), new ActionController\Routing\Path('GET', 'accounts/72'));
    $this->assert_equal(account_url(72),  new ActionController\Routing\Url('GET', 'accounts/72'));

    # :format
    $this->assert_true(function_exists('formatted_accounts_path'));
    $this->assert_true(function_exists('formatted_account_path'));
    $this->assert_true(function_exists('formatted_new_account_path'));
    $this->assert_true(function_exists('formatted_edit_account_path'));
    
    $this->assert_true(function_exists('formatted_accounts_url'));
    $this->assert_true(function_exists('formatted_account_url'));
    $this->assert_true(function_exists('formatted_new_account_url'));
    $this->assert_true(function_exists('formatted_edit_account_url'));
    
    $this->assert_equal(formatted_account_path(array(':id' => 73, ':format' => 'html')),
      new ActionController\Routing\Path('GET', 'accounts/73.html'));
    $this->assert_equal(formatted_account_url(array(':id' => 73, ':format' => 'xml')),
      new ActionController\Routing\Url('GET', 'accounts/73.xml'));

    # edge case: resources name is singular
    $this->assert_true(function_exists('wiki_index_path'));
    $this->assert_true(function_exists('formatted_wiki_index_path'));
    
    $this->assert_equal(wiki_index_path(), new ActionController\Routing\Path('GET', 'wiki'));
    $this->assert_equal(edit_wiki_url(1), new ActionController\Routing\Url('GET', 'wiki/1/edit'));
  }

  function test_named_routes_for_resource()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_true(function_exists('geocoder_path'));
    $this->assert_true(function_exists('new_geocoder_path'));
    $this->assert_true(function_exists('edit_geocoder_path'));
    
    $this->assert_true(function_exists('geocoder_url'));
    $this->assert_true(function_exists('new_geocoder_url'));
    $this->assert_true(function_exists('edit_geocoder_url'));
    
    $this->assert_equal(geocoder_path(), new ActionController\Routing\Path('GET', 'geocoder'));
    $this->assert_equal(geocoder_url(),  new ActionController\Routing\Url('GET', 'geocoder'));
    
    $this->assert_equal(new_geocoder_path(), new ActionController\Routing\Path('GET', 'geocoder/new'));
    $this->assert_equal(new_geocoder_url(),  new ActionController\Routing\Url('GET', 'geocoder/new'));
    
    $this->assert_equal(edit_geocoder_path(), new ActionController\Routing\Path('GET', 'geocoder/edit'));
    $this->assert_equal(edit_geocoder_url(),  new ActionController\Routing\Url('GET', 'geocoder/edit'));
    
    # :format
    $this->assert_true(function_exists('formatted_geocoder_path'));
    $this->assert_true(function_exists('formatted_new_geocoder_path'));
    $this->assert_true(function_exists('formatted_edit_geocoder_path'));
    
    $this->assert_true(function_exists('formatted_geocoder_url'));
    $this->assert_true(function_exists('formatted_new_geocoder_url'));
    $this->assert_true(function_exists('formatted_edit_geocoder_url'));
    
    $this->assert_equal(formatted_geocoder_path(array(':id' => 73, ':format' => 'html')),
      new ActionController\Routing\Path('GET', 'geocoder.html'));
    $this->assert_equal(formatted_geocoder_url(array(':id' => 73, ':format' => 'xml')),
      new ActionController\Routing\Url('GET', 'geocoder.xml'));
  }
  
  function test_named_routes_when_index_and_show_are_missing()
  {
    # named route for create when index is disabled
    $this->assert_true(function_exists('categories_path'));
    $this->assert_true(function_exists('categories_url'));
    $this->assert_equal((string)categories_path(), '/kategorien');
    
    # named route for update when show is disabled
    $this->assert_true(function_exists('category_path'));
    $this->assert_true(function_exists('category_url'));
    $this->assert_equal((string)category_path(1), '/kategorien/1');
    
    # named route for delete when show & update are disabled
    $this->assert_true(function_exists('tag_path'));
    $this->assert_true(function_exists('tag_url'));
    $this->assert_equal((string)tag_path(2), '/tags/2');
  }
  
  function test_nested_resources()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_equal($map->route('POST', 'discussions/31/messages'), array(
      ':method' => 'POST', ':controller' => 'messages', ':action' => 'create', ':discussion_id' => 31, ':format' => null));
    
    $this->assert_equal($map->route('GET', 'discussions/32/messages/234/edit.xml'), array(
      ':method' => 'GET', ':controller' => 'messages', ':action' => 'edit', ':discussion_id' => 32, ':id' => 234, ':format' => 'xml'));
    
    $this->assert_equal($map->route('PUT', 'discussions/32/messages/234.json'), array(
      ':method' => 'PUT', ':controller' => 'messages', ':action' => 'update', ':discussion_id' => 32, ':id' => 234, ':format' => 'json'));
    
    $this->assert_equal($map->route('DELETE', 'discussions/32/messages/234'), array(
      ':method' => 'DELETE', ':controller' => 'messages', ':action' => 'delete', ':discussion_id' => 32, ':id' => 234, ':format' => null));
    
    # has_many
    $this->assert_equal((string)discussions_path(), '/discussions');
    $this->assert_equal(discussion_messages_path(array(':discussion_id' => 34)),
      new ActionController\Routing\Path('GET', 'discussions/34/messages'));
    $this->assert_equal(new_discussion_message_path(array(':discussion_id' => 43)),
      new ActionController\Routing\Path('GET', 'discussions/43/messages/new'));
    $this->assert_equal(discussion_message_path(array(':discussion_id' => 46, ':id' => 12)),
      new ActionController\Routing\Path('GET', 'discussions/46/messages/12'));
    $this->assert_equal(edit_discussion_message_path(array(':discussion_id' => 13, ':id' => 26)),
      new ActionController\Routing\Path('GET', 'discussions/13/messages/26/edit'));
    
    # has_one
    $this->assert_equal(discussion_author_path(array(':discussion_id' => 34)),
      new ActionController\Routing\Path('GET', 'discussions/34/author'));
    $this->assert_equal(new_discussion_author_path(array(':discussion_id' => 43)),
      new ActionController\Routing\Path('GET', 'discussions/43/author/new'));
    $this->assert_equal(discussion_author_path(array(':discussion_id' => 46)),
      new ActionController\Routing\Path('GET', 'discussions/46/author'));
    $this->assert_equal(edit_discussion_author_path(array(':discussion_id' => 13)),
      new ActionController\Routing\Path('GET', 'discussions/13/author/edit'));
    
    # closure: resources
    $this->assert_equal((string)events_path(), '/events');
    $this->assert_equal(event_tickets_path(array(':event_id' => 12)),
      new ActionController\Routing\Path('GET', 'events/12/tickets'));
    $this->assert_equal(new_event_ticket_path(array(':event_id' => 12)),
      new ActionController\Routing\Path('GET', 'events/12/tickets/new'));
    $this->assert_equal(event_ticket_path(array(':event_id' => 12, ':id' => 5)),
      new ActionController\Routing\Path('GET', 'events/12/tickets/5'));
    $this->assert_equal(edit_event_ticket_path(array(':event_id' => 13,':id' => 3)),
      new ActionController\Routing\Path('GET', 'events/13/tickets/3/edit'));
    
    # closure: resource
    $this->assert_equal(event_description_path(array(':event_id' => 13)),
      new ActionController\Routing\Path('GET', 'events/13/about'));
    $this->assert_equal(new_event_description_path(array(':event_id' => 14)),
      new ActionController\Routing\Path('GET', 'events/14/about/new'));
    $this->assert_equal(edit_event_description_path(array(':event_id' => 17)),
      new ActionController\Routing\Path('GET', 'events/17/about/edit'));
    
    # resource closure + controller + as
    $this->assert_equal(profile_picture_path(),
      new ActionController\Routing\Path('GET', 'profil/picture'));
  }
  
  function test_name_space()
  {
    $map = ActionController\Routing\Routes::draw();
    
    $this->assert_equal($map->route('GET', 'admin/products'), array(
      ':method' => 'GET', ':controller' => 'admin\products', ':action' => 'index', ':format' => null));
    
    $this->assert_equal($map->route('GET', 'admin/products/1'), array(
      ':method' => 'GET', ':controller' => 'admin\products', ':action' => 'show', ':id' => 1, ':format' => null));
    
    $this->assert_equal($map->route('GET', 'admin/products/1/invoices'), array(
      ':method' => 'GET', ':controller' => 'admin\invoices', ':action' => 'index', ':product_id' => 1, ':format' => null));
    
    $this->assert_equal($map->route('GET', 'admin/products/1/invoices/2'), array(
      ':method' => 'GET', ':controller' => 'admin\invoices', ':action' => 'show', ':product_id' => 1, ':id' => 2, ':format' => null));
    
    $this->assert_equal($map->route('PUT', 'admin/products/1/invoices/2/validate'), array(
      ':method' => 'PUT', ':controller' => 'admin\invoices', ':action' => 'validate', ':product_id' => 1, ':id' => 2, ':format' => null));
    
    $this->assert_equal($map->route('GET', 'admin/options/edit.xml'), array(
      ':method' => 'GET', ':controller' => 'admin\options', ':action' => 'edit', ':format' => 'xml'));
    
    $this->assert_equal($map->route('PUT', 'admin/options'), array(
      ':method' => 'PUT', ':controller' => 'admin\options', ':action' => 'update', ':format' => null));
    
    $this->assert_equal($map->route('DELETE', 'admin/options'), array(
      ':method' => 'DELETE', ':controller' => 'admin\options', ':action' => 'delete', ':format' => null));
    
    $this->assert_not_equal($map->route('GET', 'admin/options'), array(
      ':method' => 'GET', ':controller' => 'admin\options', ':action' => 'index', ':format' => null));
  }
}

?>
