<?php

$location = dirname(__FILE__).'/../../../..';
$_SERVER['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/tag_helper.php";
require_once MISAGO."/lib/action_view/helpers/form_tag_helper.php";
require_once MISAGO."/lib/action_view/helpers/url_helper.php";

class Test_ActionView_Helpers_UrlHelper extends Unit_Test
{
  function test_link_to()
  {
    $this->assert_equal("root", link_to('abcd', '/'), '<a href="/">abcd</a>');
    $this->assert_equal("absolute path", link_to('azerty', '/page/123'), '<a href="/page/123">azerty</a>');
    $this->assert_equal("attributes", link_to('azerty', '/page/123', array('class' => 'toto')), '<a class="toto" href="/page/123">azerty</a>');
    $this->assert_equal("other attributes", link_to('azerty', '/posts/tag/abcd', array('rel' => 'tag')), '<a rel="tag" href="/posts/tag/abcd">azerty</a>');
    
    $this->assert_equal("absolute URL + attributes", link_to('azerty', 'http://www.domain.com/posts/tag/abcd', array('rel' => 'tag')),
      '<a rel="tag" href="http://www.domain.com/posts/tag/abcd">azerty</a>');
    
    $this->assert_equal("resolved URL", link_to('categories', array(':controller' => 'archives', ':action' => 'categories')),
      '<a href="/archives/categories">categories</a>');
    $this->assert_equal("resolved URL + query string", link_to('products (desc)', array(':controller' => 'products', 'order' => 'desc')),
      '<a href="/products?order=desc">products (desc)</a>');
    
    $html_link = link_to('delete me', new ActionController_Path('DELETE', 'page/123'));
    $this->assert_equal("Generated path", $html_link, '<a class="request_method:delete" href="/page/123">delete me</a>');
    
    $html_link = link_to('destroy me', new ActionController_Path('DELETE', 'page/123'), array('class' => 'destroy'));
    $this->assert_equal("Generated path + attribute", $html_link, '<a class="destroy request_method:delete" href="/page/123">destroy me</a>');
    
    $html_link = link_to('update me', new ActionController_URL('PUT', 'page/456'));
    $this->assert_equal("Generated URL", $html_link, '<a class="request_method:put" href="http://localhost:3009/page/456">update me</a>');
  }
  
  function test_current_page()
  {
    $_SERVER['REQUEST_URI'] = '/archives';
    $this->assert_true('',  current_page('/archives'));
    $this->assert_false('', current_page('/articles'));
    $this->assert_true('',  current_page(array(':controller' => 'archives')));
    $this->assert_false('', current_page(array(':controller' => 'accounts')));
    $this->assert_false('', current_page(array(':controller' => 'archives', ':action' => 'categories')));
    
    $_SERVER['REQUEST_URI'] = '/articles/show/45';
    $this->assert_false('with params: failure', current_page(array(':controller' => 'archives', ':action' => 'show', ':id' => 45)));
    $this->assert_true('with params: success',  current_page(array(':controller' => 'articles', ':action' => 'show', ':id' => 45)));
    
    $_SERVER['REQUEST_URI'] = '/articles?order=desc';
    $this->assert_true('true even without GET params', current_page(array(':controller' => 'articles')));
    $this->assert_false('wrong GET param', current_page(array(':controller' => 'articles', 'order' => 'asc')));
    $this->assert_true('good GET params',  current_page(array(':controller' => 'articles', 'order' => 'desc')));
  }
  
  function test_link_to_unless_current()
  {
    $map = ActionController_Routing::draw();
    $map->reset();
    $map->connect(':controller/:action/:id.:format');
    
    $_SERVER['REQUEST_URI'] = '/archives';
    $this->assert_equal('same page', link_to_unless_current('Archives', '/archives'),
      '<span>Archives</span>');
    $this->assert_equal('other page', link_to_unless_current('Products', '/products'),
      '<a href="/products">Products</a>');
    $this->assert_equal('', link_to_unless_current('archives', array(':controller' => 'archives')),
      '<span>archives</span>');
    $this->assert_equal('', link_to_unless_current('List', array(':controller' => 'accounts')),
      '<a href="/accounts">List</a>');
    $this->assert_equal('', link_to_unless_current('categories', array(':controller' => 'archives', ':action' => 'categories')),
      '<a href="/archives/categories">categories</a>');
    
    $_SERVER['REQUEST_URI'] = '/articles/show/45';
    $this->assert_equal('with params: failure', link_to_unless_current('more details', array(':controller' => 'products', ':action' => 'show', ':id' => 45)),
      '<a href="/products/show/45">more details</a>');
    $this->assert_equal('with params: success', link_to_unless_current('article', array(':controller' => 'articles', ':action' => 'show', ':id' => 45)),
      '<span>article</span>');
    
    $_SERVER['REQUEST_URI'] = '/articles?order=desc';
    $this->assert_equal('true even without GET params', link_to_unless_current('articles', array(':controller' => 'articles')),
      '<span>articles</span>');
    $this->assert_equal('wrong GET param', link_to_unless_current('articles (asc)', array(':controller' => 'articles', 'order' => 'asc')),
      '<a href="/articles?order=asc">articles (asc)</a>');
    $this->assert_equal('good GET params',  link_to_unless_current('articles (desc)', array(':controller' => 'articles', 'order' => 'desc')),
      '<span>articles (desc)</span>');
  }
  
  function test_button_to()
  {
    $html = button_to('new', array(':controller' => 'products', ':action' => 'new'));
    $this->assert_equal('basic parameters', $html, '<form action="/products/new" method="post" class="button-to">'.
      '<div><input type="submit" value="new"/></div>'.
      '</form>');
    
    $html = button_to('new', array(':controller' => 'products', ':action' => 'delete', ':id' => 2), array('method' => 'delete', 'confirm' => 'Are you sure?'));
    $this->assert_equal('basic parameters', $html, '<form action="/products/delete/2" method="post" class="button-to" onsubmit="return confirm(\'Are you sure?\');">'.
      '<input type="hidden" name="_method" value="delete"/>'.
      '<div><input type="submit" value="new"/></div>'.
      '</form>');
  }
  
  function test_mail_to()
  {
    
  }
}

new Test_ActionView_Helpers_UrlHelper();

?>
