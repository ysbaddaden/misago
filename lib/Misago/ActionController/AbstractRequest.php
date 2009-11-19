<?php
namespace Misago\ActionController;

# Request data.
# 
# <tt>ActionController_CgiRequest</tt> and <tt>ActionController_TestRequet</tt>
# proposes an actual implementation.
interface AbstractRequest
{
#  public $headers;
  
  # Returns the list of accepted format.
  function accepts();
  
  # Returns the content-type of posted data.
  function content_type();
  
  function format($force_format=null);
  
  # Returns the lowercased method for current request.
  function method();
  
  # Returns the used protocol as 'http://' or 'https://'.
  function protocol();
  
  # Wether the request it HTTPS or not.
  function is_ssl();
  
  # Host from request URI.
  function host();
  
  # Port from request URI.
  function port();
  
  # Returns ':port' as string, if not standard ports (80 or 443).
  # Otherwise returns the empty string.
  function port_string();
  
  function subdomains();
  
  # Path from request URI.
  function path();
  
  # Tries to determine the relative root, if site is not hosted at server's root.
  function relative_url_root();
  
  # Returns user's IP, trying to bypass proxies.
  function remote_ip();
  
  # Returns merged get and post parameters.
  function & parameters();
  
  # Returns raw post body.
  function raw_body();
  
  # Checks wether the request originates from XMLHttpRequest.
  function is_xml_http_request();
}

?>
