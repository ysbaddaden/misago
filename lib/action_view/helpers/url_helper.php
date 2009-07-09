<?php
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

# Returns the URL for a given mapping.
# 
# @namespace ActionView_Helpers_UrlHelper
function url_for($mapping)
{
  $map = ActionController_Routing::draw();
  return $map->reverse($mapping);
}

# TODO: mail_to()
# @namespace ActionView_Helpers_UrlHelper
function mail_to()
{
  
}

# TODO: is_current_page()
# @namespace ActionView_Helpers_UrlHelper
function is_current_page()
{
  
}

# TODO: button_to()
# @namespace ActionView_Helpers_UrlHelper
function button_to()
{
  
}

# TODO: link_to_unless_current()
# @namespace ActionView_Helpers_UrlHelper
function link_to_unless_current()
{
  
}

?>
