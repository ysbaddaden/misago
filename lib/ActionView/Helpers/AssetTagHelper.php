<?php

# :nodoc:
function ActionView_Helpers_AssetTag_linearize_path($base_path, $path)
{
  if (is_array($path)) {
    return url_for($path);
  }
  
  $path = trim($path);
  if (strpos($path, 'http://') !== 0)
  {
    if (strpos($path, '/') !== 0) {
      $path = "$base_path/$path";
    }
    if (file_exists(ROOT.'/public'.$path)) {
      $path .= '?'.filemtime(ROOT.'/public'.$path);
    }
  }
  return $path;
}

# Creates a LINK tag for an auto-detectable feed link (either RSS or ATOM).
# 
# Available options:
# 
# - +rel+   - defaults to alternate
# - +type+  - mime-type
# - +title+ - specify the title (defaults to the type)
# 
# Examples:
# 
#   auto_discovery_link_tag('rss', products_url(array(':format' => 'rss')));
#   auto_discovery_link_tag('atom', blog_posts_url(array(':format' => 'xml')),
#     array('title' => 'Subscribe to this blog'));
# 
# :namespace: ActionView\Helpers\AssetTag
function auto_discovery_link_tag($type='rss', $url=null, $attributes=array())
{
  $attributes = array_merge(array(
    'rel'   => 'alternate',
    'type'  => ($type == 'rss') ? 'application/rss+xml' : 'application/atom+xml',
    'href'  => $url,
    'title' => strtoupper($type),
  ), $attributes);
  return tag('link', $attributes);
}

# Linearizes an image path.
# 
#   image_path('logo.jpg');          # /img/logo.jpg
#   image_path('/path/to/logo.jpg'); # /path/to/logo.jpg
#   image_path('http://mybrand.com/path/to/logo.jpg'); # http://mybrand.com/path/to/logo.jpg
# 
# :namespace: ActionView\Helpers\AssetTag
function image_path($src)
{
  return ActionView_Helpers_AssetTag_linearize_path('/img', $src);
}

# Linearizes a javascript path.
# 
#   javascript_path('logo.js');          # /js/logo.js
#   javascript_path('/path/to/logo.js'); # /path/to/logo.js
#   javascript_path('http://mybrand.com/logo.js'); # http://mybrand.com/logo.js
# 
# :namespace: ActionView\Helpers\AssetTag
function javascript_path($src)
{
  return ActionView_Helpers_AssetTag_linearize_path('/js', $src);
}

# Linearizes a stylesheet path.
# 
#   stylesheet_path('logo.css');          # /js/logo.css
#   stylesheet_path('/path/to/logo.css'); # /path/to/logo.css
#   stylesheet_path('http://mybrand.com/css/logo.css'); # http://mybrand.com/css/logo.css
# 
# :namespace: ActionView\Helpers\AssetTag
function stylesheet_path($href)
{
  return ActionView_Helpers_AssetTag_linearize_path('/css', $href);
}

# Renders an IMG tag.
# 
#   image_tag('logo.jpg');
#   image_tag('logo.jpg', array('alt' => 'my logo',
#     'title' => "Ain't my logo pretty?", 'class' => 'brand'));
# 
# :namespace: ActionView\Helpers\AssetTag
function image_tag($src, $attributes=null)
{
  $attributes['src'] = image_path($src);
  if (!isset($attributes['alt'])) {
    $attributes['alt'] = '';
  }
  return tag('img', $attributes);
}

# Includes one or more javascript files.
# 
#   javascript_include_tag('app.js');
#   javascript_include_tag('framework.js', 'app.js');
# 
# :namespace: ActionView\Helpers\AssetTag
function javascript_include_tag($args)
{
  $sources = func_get_args();
  $tags    = array();
  foreach($sources as $src)
  {
    $attributes = array(
      'type' => 'text/javascript',
      'charset' => 'utf-8',
      'src' => javascript_path($src),
    );
    $tags[] = tag('script', '', $attributes);
  }
  return implode("\n", $tags);
}

# Includes one or more stylesheets.
# 
#   stylesheet_link_tag('reset.css');
#   stylesheet_link_tag('reset.css', 'typography.css');
#   stylesheet_link_tag('print.css', array('media' => 'print'));
# 
# :namespace: ActionView\Helpers\AssetTag
function stylesheet_link_tag($args)
{
  $sources = func_get_args();
  $options = is_array($sources[count($sources) - 1]) ? array_pop($sources) : array();
  $tags    = array();
  
  foreach($sources as $href)
  {
    $attributes = array_merge($options, array(
      'rel' => 'stylesheet',
      'type' => 'text/css',
      'charset' => 'utf-8',
      'href' => stylesheet_path($href),
    ));
    $tags[] = tag('link', $attributes);
  }
  return implode("\n", $tags);
}

?>
