<?php

# Checks wether the given URL is the current page or not.
# 
# Let's say we are visiting `/products?order=asc`:
# 
#   current_page('/products')
#   # => true
# 
#   current_page(array(':controller' => '/products'))
#   # => true
# 
#   current_page(array(':controller' => '/products', 'order' => 'desc'))
#   # => false
# 
#   current_page(array(':controller' => '/products', 'order' => 'asc'))
#   # => true
# 
#   current_page(array(':controller' => '/products', ':action' => 'show'))
#   # => false
# 
# @namespace ActionView_Helpers_UrlHelper
function current_page($url)
{
  if (!is_string($url)) {
    $url = (string)path_for($url);
  }
  
  # URL has no query string? let's compare the path only:
  if (strpos($url, '?') === false)
  {
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return ($url == $request_uri);
  }
  return ($url == $_SERVER['REQUEST_URI']);
}

# Creates a link.
# 
# @namespace ActionView_Helpers_UrlHelper
function link_to($content, $url, $attributes=null)
{
  if (is_object($url) and isset($url->method))
  {
    $method = strtolower($url->method);
    if ($url->method != 'GET')
    {
      if (isset($attributes['class'])) {
        $attributes['class'] .= ' request_method:'.$method;
      }
      else {
        $attributes['class'] = 'request_method:'.$method;
      }
    }
  }
  $attributes['href'] = $url;
  return tag('a', $content, $attributes);
}

# Renders a link unless it points to the current page. Otherwise
# displays the content into a SPAN tag with no attributes.
# 
# @namespace ActionView_Helpers_UrlHelper
function link_to_unless_current($content, $url, $attributes=null)
{
  if (current_page($url)) {
    return tag('span', $content);
  }
  return link_to($content, $url, $attributes);
}

# TODO: button_to()
# @namespace ActionView_Helpers_UrlHelper
function button_to()
{
  
}

# TODO: mail_to()
# @namespace ActionView_Helpers_UrlHelper
function mail_to()
{
  
}


?>
