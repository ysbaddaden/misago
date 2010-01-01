<?php
require_once __DIR__.'/../../../unit.php';
require_once MISAGO."/lib/Misago/ActionView/Helpers/TagHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/AssetTagHelper.php";

class Test_ActionView_Helper_AssetTagHelper extends Misago\Unit\Test
{
  function test_image_path()
  {
    $this->assert_equal(image_path('logo.png'), '/img/logo.png');
    $this->assert_equal(image_path('website/logo.png'), '/img/website/logo.png');
    $this->assert_equal(image_path('/another-logo.jpg'), '/another-logo.jpg');
    $this->assert_equal(image_path('http://toto.com/my-logo.gif'), 'http://toto.com/my-logo.gif');
    $this->assert_equal(image_path('   my-logo.gif '), '/img/my-logo.gif');
    
    $this->assert_equal(image_path('timestamp.jpg'),
      '/img/timestamp.jpg?'.filemtime(ROOT.'/public/img/timestamp.jpg'));
    
    $this->assert_equal(javascript_path(array(':controller' => 'stories', ':action' => 'avatar', ':format' => 'jpg')),
      '/stories/avatar.jpg');
  }
  
  function test_javascript_path()
  {
    $this->assert_equal(javascript_path('logo.js'), '/js/logo.js');
    $this->assert_equal(javascript_path('website/kokone.js'), '/js/website/kokone.js');
    $this->assert_equal(javascript_path('/another-framework.js'), '/another-framework.js');
    $this->assert_equal(javascript_path('http://arthur.com/kokone.js'), 'http://arthur.com/kokone.js');
    $this->assert_equal(javascript_path('   kokone.js '), '/js/kokone.js');
    $this->assert_equal(javascript_path('timestamp.js'), '/js/timestamp.js?'.filemtime(ROOT.'/public/js/timestamp.js'));
    
    $this->assert_equal(javascript_path(array(':controller' => 'stories', ':action' => 'widget', ':format' => 'js')),
      '/stories/widget.js');
  }
  
  function test_stylesheet_path()
  {
    $this->assert_equal(stylesheet_path('reset.css'), '/css/reset.css');
    $this->assert_equal(stylesheet_path('website/brand.css'), '/css/website/brand.css');
    $this->assert_equal(stylesheet_path('/path/to/blog.css'), '/path/to/blog.css');
    $this->assert_equal(stylesheet_path('http://toto.com/theme.css'), 'http://toto.com/theme.css');
    $this->assert_equal(stylesheet_path('   brand.css '), '/css/brand.css');
    $this->assert_equal(stylesheet_path('timestamp.css'), '/css/timestamp.css?'.filemtime(ROOT.'/public/css/timestamp.css'));
    $this->assert_equal(javascript_path(array(':controller' => 'stories', ':action' => 'custom_design', ':format' => 'css')),
      '/stories/custom_design.css');
  }
  
  function test_image_tag()
  {
    $this->assert_equal(image_tag('logo.png'), '<img src="/img/logo.png" alt=""/>');
    $this->assert_equal(image_tag('/another-logo.jpg', array('alt' => 'another logo')),
      '<img alt="another logo" src="/another-logo.jpg"/>'
    );
    $this->assert_equal(image_tag('http://toto.com/my-logo.gif', array('class' => 'logo', 'id' => "brand")),
      '<img class="logo" id="brand" src="http://toto.com/my-logo.gif" alt=""/>'
    );
    $this->assert_equal(image_tag('logo.jpg', array('alt' => 'my logo', 'title' => "Ain't my logo pretty?", 'class' => 'brand')),
      '<img alt="my logo" title="Ain\'t my logo pretty?" class="brand" src="/img/logo.jpg"/>');
  }
  
  function test_javascript_include_tag()
  {
    $this->assert_equal(javascript_include_tag('app.js'),
      '<script type="text/javascript" charset="utf-8" src="/js/app.js"></script>');

    $this->assert_equal(javascript_include_tag('test/app.js'),
      '<script type="text/javascript" charset="utf-8" src="/js/test/app.js"></script>');
    
    $this->assert_equal(javascript_include_tag('/files/user/app.js'),
      '<script type="text/javascript" charset="utf-8" src="/files/user/app.js"></script>');

    $this->assert_equal(javascript_include_tag('http://toto.com/js/app.js'),
      '<script type="text/javascript" charset="utf-8" src="http://toto.com/js/app.js"></script>');

    $this->assert_equal(javascript_include_tag('framework.js', 'app.js'),
      '<script type="text/javascript" charset="utf-8" src="/js/framework.js"></script>'."\n".
      '<script type="text/javascript" charset="utf-8" src="/js/app.js"></script>');
  }
  
  function test_stylesheet_link_tag()
  {
     $this->assert_equal(stylesheet_link_tag('app.css'),
      '<link rel="stylesheet" type="text/css" charset="utf-8" href="/css/app.css"/>');
    
    $this->assert_equal(stylesheet_link_tag('test/app.css'),
      '<link rel="stylesheet" type="text/css" charset="utf-8" href="/css/test/app.css"/>');
    
    $this->assert_equal(stylesheet_link_tag('/files/user/app.css'),
      '<link rel="stylesheet" type="text/css" charset="utf-8" href="/files/user/app.css"/>');

    $this->assert_equal(stylesheet_link_tag('http://toto.com/css/app.css'),
      '<link rel="stylesheet" type="text/css" charset="utf-8" href="http://toto.com/css/app.css"/>');

    $this->assert_equal(stylesheet_link_tag('framework.css', 'app.css'),
      '<link rel="stylesheet" type="text/css" charset="utf-8" href="/css/framework.css"/>'."\n".
      '<link rel="stylesheet" type="text/css" charset="utf-8" href="/css/app.css"/>');
    
    $this->assert_equal(stylesheet_link_tag('app.css', array('media' => 'screen')),
      '<link media="screen" rel="stylesheet" type="text/css" charset="utf-8" href="/css/app.css"/>');
    
    $this->assert_equal(stylesheet_link_tag('app.css', array('media' => 'print')),
      '<link media="print" rel="stylesheet" type="text/css" charset="utf-8" href="/css/app.css"/>');
  }
  
  function test_auto_discovery_link_tag()
  {
    $this->assert_equal(auto_discovery_link_tag('rss', '/products.rss'),
      '<link rel="alternate" type="application/rss+xml" href="/products.rss" title="RSS"/>');
    $this->assert_equal(auto_discovery_link_tag('atom', '/blog/posts.xml', array('title' => 'Subscribe to this blog')),
      '<link rel="alternate" type="application/atom+xml" href="/blog/posts.xml" title="Subscribe to this blog"/>');
    $this->assert_equal(auto_discovery_link_tag('atom', '/posts.xml', array('rel' => 'something', 'type' => 'text/xml')),
      '<link rel="something" type="text/xml" href="/posts.xml" title="ATOM"/>');
  }
}

?>
