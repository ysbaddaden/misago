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
#   current_page(array(':controller' => '/products', ':action' => 'show'))
#   # => false
# 
#   current_page(array(':controller' => '/products', 'order' => 'asc'))
#   # => true
# 
#   current_page(array(':controller' => '/products', 'order' => 'desc'))
#   # => false
# 
# @namespace ActionView_Helpers_UrlHelper
function current_page($url)
{
  if (!$url instanceof ActionController_URL
    and !$url instanceof ActionController_Path
    and !is_string($url))
  {
    $url = url_for($url);
  }
  $url = (string)$url;
  
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
# Example:
# 
#   link_to('full list', '/products')
#   # => <a href="/products">full list</a>
# 
# Resolving a route:
# 
#   link_to("read more", array(':controller' => 'posts', ':action' => 'show', ':id' => 1))
#   # => <a href="/posts/show/1#comments">read more</a>
# 
# Using named routes:
# 
#   link_to($article->title, show_article_path($article->id))
#   # => <a href="/article/1">my article</a>
# 
# You may add attributes:
# 
#   link_to('full list', '/products', array('class' => 'internal'))
#   # => <a class="internal" href="/products">full list</a>
# 
# You also may add a query string and an anchor:
# 
#   link_to("posts' comments", array(':controller' => 'posts', 'anchor' => 'comments', 'year' => 2008))
#   # => <a href="/posts?year=2008#comments">posts' comments</a>
# 
# Options:
# 
# - confirm: adds a JavaScript confirm dialog.
# 
# @namespace ActionView_Helpers_UrlHelper
function link_to($content, $url=null, $attributes=null)
{
  if ($url === null) {
    $url = $content;
  }
  else
  {
    # resolves URL
    if(is_array($url)) {
      $url = url_for($url);
    }
    
    # URL is URI+method
    if (is_object($url) and isset($url->method)) {
      $method = $url->method;
    }
    if (isset($attributes['method']))
    {
      $method = $attributes['method'];
      unset($attributes['method']);
    }
    if (isset($method))
    {
      $method = strtoupper($method);
      if ($method != 'GET')
      {
        $onclick = "var f = document.createElement('form'); ".
          "f.action = this.href; ".
          "f.method = 'POST'; ".
          "var m = document.createElement('input'); ".
          "m.setAttribute('type', 'hidden'); ".
          "m.setAttribute('name', '_method'); ".
          "m.setAttribute('value', '$method'); ".
          "f.appendChild(m); ".
          "f.style.display='none'; ".
          "this.parentNode.appendChild(f); ".
          "f.submit()";
      }
    }
  }
  
  if (isset($attributes['confirm']))
  {
    $confirm = addslashes($attributes['confirm']);
    unset($attributes['confirm']);
    
    $attributes['onclick'] = isset($onclick) ?
      "if (confirm('$confirm')) { $onclick; } return false;" :
      $attributes['onclick'] = "return confirm('$confirm');";
  }
  elseif (isset($onclick)) {
    $attributes['onclick'] = "$onclick; return false;";
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

# Generates a form with a single button that submits to the given URL.
# 
# Special attributes:
# 
# - method: forces HTTP method
# - confirm: asks for JavaScript confirmation before submitting form
# 
# @namespace ActionView_Helpers_UrlHelper
function button_to($name, $url, $attributes=null)
{
  if (is_array($url)) {
    $url = url_for($url);
  }
  
  $form_attributes = array('class' => 'button-to');
  
  if (isset($attributes['confirm']))
  {
    $confirm = htmlspecialchars(str_replace("'", "\'", $attributes['confirm']));
    unset($attributes['confirm']);
    $form_attributes['onsubmit'] = "return confirm('$confirm');";
  }
  
  if (isset($attributes['method']))
  {
    $form_attributes['method'] = $attributes['method'];
    unset($attributes['method']);
  }
  
  $str  = form_tag($url, $form_attributes);
  $str .= '<div>'.submit_tag($name, $attributes).'</div>';
  $str .= '</form>';
  return $str;
}

# Generates a mailto link.
# 
#   mail_to('me@domain.com');
#   # => <a href="mailto:me@domain.com">me@domain.com</a>
#   
#   mail_to('me@domain.com', 'myself');
#   # => <a href="mailto:me@domain.com">myself</a>
#   
#   mail_to('me@domain.com', 'another', array('class' => 'email'));
#   # => <a class="email" href="mailto:me@domain.com">another</a>
# 
# @namespace ActionView_Helpers_UrlHelper
function mail_to($email, $name=null, $options=null)
{
  return link_to($name === null ? $email : $name, "mailto:$email", $options);
}

?>
