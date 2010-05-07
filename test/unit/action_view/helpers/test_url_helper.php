<?php
require_once __DIR__.'/../../../unit.php';
require_once MISAGO."/lib/Misago/ActionView/Helpers/TagHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/FormTagHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/UrlHelper.php";
use Misago\ActionController;

class Test_ActionView_Helpers_UrlHelper extends Misago\Unit\TestCase
{
  function test_link_to()
  {
    $this->assert_equal(link_to('abcd', '/'), '<a href="/">abcd</a>');
    $this->assert_equal(link_to('azerty', '/page/123'), '<a href="/page/123">azerty</a>');
    $this->assert_equal(link_to('azerty', '/page/123', array('class' => 'toto')), '<a class="toto" href="/page/123">azerty</a>');
    $this->assert_equal(link_to('azerty', '/posts/tag/abcd', array('rel' => 'tag')), '<a rel="tag" href="/posts/tag/abcd">azerty</a>');
    
    $this->assert_equal(link_to('categories', array(':controller' => 'archives', ':action' => 'categories')),
      '<a href="/archives/categories">categories</a>');
    
    $this->assert_equal(link_to('products (desc)',
      array(':controller' => 'products', 'order' => 'desc')),
      '<a href="/products?order=desc">products (desc)</a>');
    $this->assert_equal(link_to('products',
      array(':controller' => 'products', 'order' => 'desc', 'field' => 'date')),
      '<a href="/products?field=date&amp;order=desc">products</a>');
    
    $this->assert_equal(link_to('azerty', 'http://www.domain.com/posts/tag/abcd', array('rel' => 'tag')),
      '<a rel="tag" href="http://www.domain.com/posts/tag/abcd">azerty</a>');
    $this->assert_equal(link_to('http://toto.com/'), '<a href="http://toto.com/">http://toto.com/</a>');
  }
  
  function test_link_to_with_non_get_methods()
  {
    $html_link = link_to('delete me', new ActionController\Routing\Path('DELETE', 'page/123'));
    $this->assert_equal($html_link, '<a onclick="var f = document.createElement(\'form\'); f.action = this.href; f.method = \'POST\'; var m = document.createElement(\'input\'); m.setAttribute(\'type\', \'hidden\'); m.setAttribute(\'name\', \'_method\'); m.setAttribute(\'value\', \'DELETE\'); f.appendChild(m); this.parentNode.appendChild(f); f.submit(); return false;" href="/page/123">delete me</a>');
    
    $html_link = link_to('destroy me', new ActionController\Routing\Path('DELETE', 'page/459'), array('class' => 'destroy'));
    $this->assert_equal($html_link, '<a class="destroy" onclick="var f = document.createElement(\'form\'); f.action = this.href; f.method = \'POST\'; var m = document.createElement(\'input\'); m.setAttribute(\'type\', \'hidden\'); m.setAttribute(\'name\', \'_method\'); m.setAttribute(\'value\', \'DELETE\'); f.appendChild(m); this.parentNode.appendChild(f); f.submit(); return false;" href="/page/459">destroy me</a>');
    
    $html_link = link_to('update me', new ActionController\Routing\Url('PUT', 'page/456'));
    $this->assert_equal($html_link, '<a onclick="var f = document.createElement(\'form\'); f.action = this.href; f.method = \'POST\'; var m = document.createElement(\'input\'); m.setAttribute(\'type\', \'hidden\'); m.setAttribute(\'name\', \'_method\'); m.setAttribute(\'value\', \'PUT\'); f.appendChild(m); this.parentNode.appendChild(f); f.submit(); return false;" href="http://localhost:3009/page/456">update me</a>');
    
    $html_link = link_to('update me', '/page/123', array('method' => 'put'));
    $this->assert_equal($html_link, '<a onclick="var f = document.createElement(\'form\'); f.action = this.href; f.method = \'POST\'; var m = document.createElement(\'input\'); m.setAttribute(\'type\', \'hidden\'); m.setAttribute(\'name\', \'_method\'); m.setAttribute(\'value\', \'PUT\'); f.appendChild(m); this.parentNode.appendChild(f); f.submit(); return false;" href="/page/123">update me</a>');
    
    $html_link = link_to('delete', new ActionController\Routing\Path('DELETE', 'posts/2'), array('method' => 'put'));
    $this->assert_equal($html_link, '<a onclick="var f = document.createElement(\'form\'); f.action = this.href; f.method = \'POST\'; var m = document.createElement(\'input\'); m.setAttribute(\'type\', \'hidden\'); m.setAttribute(\'name\', \'_method\'); m.setAttribute(\'value\', \'PUT\'); f.appendChild(m); this.parentNode.appendChild(f); f.submit(); return false;" href="/posts/2">delete</a>');
  }
  
  function test_link_to_with_javascript_confirm()
  {
    $html_link = link_to('read me', '/posts/1', array('confirm' => 'Are you sure?'));
    $this->assert_equal($html_link, '<a onclick="return confirm(\'Are you sure?\');" href="/posts/1">read me</a>');
    
    $html_link = link_to('delete', new ActionController\Routing\Path('DELETE', 'posts/1'), array('confirm' => 'Are you sure?'));
    $this->assert_equal($html_link, '<a onclick="if (confirm(\'Are you sure?\')) { var f = document.createElement(\'form\'); f.action = this.href; f.method = \'POST\'; var m = document.createElement(\'input\'); m.setAttribute(\'type\', \'hidden\'); m.setAttribute(\'name\', \'_method\'); m.setAttribute(\'value\', \'DELETE\'); f.appendChild(m); this.parentNode.appendChild(f); f.submit(); } return false;" href="/posts/1">delete</a>');
    
    $html_link = link_to('delete', '/posts/2', array('confirm' => 'sample text with "quotes" and \'single quotes\''));
    $this->assert_equal($html_link, '<a onclick="return confirm(\'sample text with \\\"quotes\\\" and \\\'single quotes\\\'\');" href="/posts/2">delete</a>');
    
    $html_link = link_to('delete', new ActionController\Routing\Path('DELETE', 'posts/1'), array('confirm' => 'sample text with \'single quotes\' and "double quotes"'));
    $this->assert_equal($html_link, '<a onclick="if (confirm(\'sample text with \\\'single quotes\\\' and \\\"double quotes\\\"\')) { var f = document.createElement(\'form\'); f.action = this.href; f.method = \'POST\'; var m = document.createElement(\'input\'); m.setAttribute(\'type\', \'hidden\'); m.setAttribute(\'name\', \'_method\'); m.setAttribute(\'value\', \'DELETE\'); f.appendChild(m); this.parentNode.appendChild(f); f.submit(); } return false;" href="/posts/1">delete</a>');
  }
  
  function test_link_to_if()
  {
    $this->assert_equal('<a href="/delete">destroy this</a>', link_to_if(true, 'destroy this', '/delete'));
    $this->assert_equal('destroy this', link_to_if(false, 'destroy this', '/delete'));
    $this->assert_equal('<a href="/events">events</a>', link_to_if(true, 'events', events_path()));
  }
  
  function test_link_to_unless()
  {
    $this->assert_equal('<a href="/delete">destroy this</a>', link_to_unless(false, 'destroy this', '/delete'));
    $this->assert_equal('destroy this', link_to_unless(true, 'destroy this', '/delete'));
    $this->assert_equal('<a href="/events">events</a>', link_to_unless(false, 'events', events_path()));
  }
  
  function test_current_page()
  {
    $_SERVER['REQUEST_URI'] = '/archives';
    $this->assert_true(current_page('/archives'));
    $this->assert_false(current_page('/articles'));
    $this->assert_true(current_page(array(':controller' => 'archives')));
    $this->assert_false(current_page(array(':controller' => 'accounts')));
    $this->assert_false(current_page(array(':controller' => 'archives', ':action' => 'categories')));
    $this->assert_true(current_page(new ActionController\Routing\Path('GET', 'archives')));
    $this->assert_false(current_page(new ActionController\Routing\Path('GET', 'articles')));
#    $this->assert_true(current_page(new ActionController\Routing\Url('GET', 'archives')));
#    $this->assert_false(current_page(new ActionController\Routing\Url('GET', 'archives/mine')));
    
    $_SERVER['REQUEST_URI'] = '/mailbox/show/45';
    $this->assert_false(current_page(array(':controller' => 'archives', ':action' => 'show', ':id' => 45)));
    $this->assert_true(current_page(array(':controller' => 'mailbox', ':action' => 'show', ':id' => 45)));
    
    $_SERVER['REQUEST_URI'] = '/mailbox?order=desc';
    $this->assert_true(current_page(array(':controller' => 'mailbox')));
    $this->assert_false(current_page(array(':controller' => 'mailbox', 'order' => 'asc')));
    $this->assert_true(current_page(array(':controller' => 'mailbox', 'order' => 'desc')));
  }
  
  function test_link_to_unless_current()
  {
    $_SERVER['REQUEST_URI'] = '/archives';
    $this->assert_equal(link_to_unless_current('Archives', '/archives'),
      '<span>Archives</span>');
    $this->assert_equal(link_to_unless_current('Products', '/products'),
      '<a href="/products">Products</a>');
    $this->assert_equal(link_to_unless_current('archives', array(':controller' => 'archives')),
      '<span>archives</span>');
    $this->assert_equal(link_to_unless_current('List', array(':controller' => 'accounts')),
      '<a href="/accounts">List</a>');
    $this->assert_equal(link_to_unless_current('categories',
      array(':controller' => 'archives', ':action' => 'categories')),
      '<a href="/archives/categories">categories</a>');
    
    $_SERVER['REQUEST_URI'] = '/articles/45';
    $this->assert_equal(link_to_unless_current('more details',
      array(':controller' => 'products', ':action' => 'show', ':id' => 45)),
      '<a href="/products/45">more details</a>');
    $this->assert_equal(link_to_unless_current('article',
      array(':controller' => 'articles', ':action' => 'show', ':id' => 45)),
      '<span>article</span>');
    
    $_SERVER['REQUEST_URI'] = '/articles?order=desc';
    $this->assert_equal(link_to_unless_current('articles', array(':controller' => 'articles')),
      '<span>articles</span>');
    $this->assert_equal(link_to_unless_current('articles (asc)', array(':controller' => 'articles', 'order' => 'asc')),
      '<a href="/articles?order=asc">articles (asc)</a>');
    $this->assert_equal(link_to_unless_current('articles (desc)', array(':controller' => 'articles', 'order' => 'desc')),
      '<span>articles (desc)</span>');
  }
  
  function test_button_to()
  {
    $html = button_to('new', array(':controller' => 'products', ':action' => 'new'));
    $this->assert_equal($html, '<form action="/products/new" method="post" class="button-to">'.
      '<div><input type="submit" value="new"/></div>'.
      '</form>');
    
    $html = button_to('new', array(':controller' => 'products', ':action' => 'new', 'field' => 'a', 'param' => 'b'));
    $this->assert_equal($html, '<form action="/products/new?field=a&amp;param=b" method="post" class="button-to">'.
      '<div><input type="submit" value="new"/></div>'.
      '</form>');
    
    $html = button_to('new', array(':controller' => 'products', ':action' => 'delete', ':id' => 2), array('method' => 'delete', 'confirm' => 'Are you sure?'));
    $this->assert_equal($html, '<form action="/products/2" method="post" class="button-to" onsubmit="return confirm(\'Are you sure?\');">'.
      '<input type="hidden" name="_method" value="delete"/>'.
      '<div><input type="submit" value="new"/></div>'.
      '</form>');
  }
  
  function test_mail_to()
  {
    $html = mail_to('me@webcomics.fr');
    $this->assert_equal($html, '<a href="mailto:me@webcomics.fr">me@webcomics.fr</a>');
    
    $html = mail_to('me@webcomics.fr', 'julien');
    $this->assert_equal($html, '<a href="mailto:me@webcomics.fr">julien</a>');
    
    $html = mail_to('another@bd-en-ligne.fr', 'sabrina', array('class' => 'email'));
    $this->assert_equal($html, '<a class="email" href="mailto:another@bd-en-ligne.fr">sabrina</a>');
  }
}

?>
