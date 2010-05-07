<?php

# Resolves an URL (reverse routing).
# 
# Options:
# 
# - +anchor+     - adds an anchor to the URL.
# - +path_only+  - false to return an absolute URI, true to return the path only (defaults to true).
# - +protocol+   - overwrites the current protocol.
# - +host+       - overwrites the current host.
# - +port+       - overwrites the current port.
# - +user+       - username for HTTP login.
# - +password+   - password for HTTP login.
# 
# Example:
# 
#   url_for(array(':controller' => 'products', ':action' => 'show', ':id' => '67'))
#   # => http://www.domain.com/products/show/67
# 
# Any unknown option that isn't a symbol is added to the query string:
# 
#   url_for(array(':controller' => 'products', 'order' => 'asc'))
#   # => http://www.domain.com/products?order=asc
# 
#   url_for(array(':controller' => 'products', ':action' => 'show', ':id' => 13, 'comments' => 'show'))
#   # => http://www.domain.com/products/show/13?comments=show
# 
# You may also add an anchor:
# 
#   url_for(array(':controller' => 'about', 'anchor' => 'me'))
#   # => http://www.domain.com/about#me
# 
# Using REST resources, you may pass an <tt>\Misago\ActiveRecord</tt> directly.
# For instance:
# 
#   $product = new Product(43);
#   $url = url_for(product);    # => http://www.domain.com/products/3
# 
# TODO: url_for: handle specified options (host, protocol, etc.)
# IMPROVE: url_for: permit for simplified calls, like url_for(array(':action' => 'index')), which shall use the current controller.
# 
# :namespace: \Misago\ActionController\Routing
function url_for($options)
{
  if ($options instanceof \Misago\ActiveRecord\Record)
  {
    $named_route = \Misago\ActiveSupport\String::underscore(get_class($options)).'_url';
    return $named_route($options);
  }
  
  $default_options = array(
    'anchor'    => null,
    'path_only' => true,
    'protocol'  => cfg_get('current_protocol'),
    'host'      => cfg_get('current_host'),
    'port'      => cfg_get('current_port'),
    'user'      => null,
    'password'  => null,
  );
  $mapping = array_diff_key($options, $default_options);
  $options = array_merge($default_options, $options);
  
  $map = \Misago\ActionController\Routing\Routes::draw();
  $keys = array();
  $query_string = array();
  foreach($mapping as $k => $v)
  {
    if (is_symbol($k)) {
      $keys[$k] = $v;
    }
    else {
      $query_string[] = "$k=".urlencode($v);
    }
  }
  $path = $map->reverse($keys);
  sort($query_string);
  
  $path .= (empty($query_string) ? '' : '?'.implode('&', $query_string)).
    (empty($options['anchor']) ? '' : "#{$options['anchor']}");
  
  if ($options['path_only']) {
    return $path;
  }
  return cfg_get('base_url').$path;
}

?>
