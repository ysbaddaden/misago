<?php

# (F)CGI requests.
# 
# CgiRequest handles HTTP headers, GET and POST parameters, etc.
# for (F)CGI requests. For instance on PUT requests, POST data isn't
# parsed. Also when using the 404-handler, QUERY_STRING isn't parsed.
# 
# CgiRequest handles all of that, and much more, transparently.
# 
# See <tt>ActionController_AbstractRequest</tt> for actual documentation.
# 
class ActionController_CgiRequest extends Misago_Object implements ActionController_AbstractRequest
{
  public    $headers;
  protected $format;
  protected $path_parameters;
  
  function __construct()
  {
    $this->parse_headers();
    $this->parse_query_string();
    if ($this->method() == 'put') {
      $this->parse_post_body();
    }
    $_REQUEST = array_merge($_GET, $_POST);
  }
  
  function accepts()
  {
    
  }
  
  function content_type()
  {
    return $_SERVER['CONTENT_TYPE'];
  }
  
  # IMPROVE: Check HTTP Accept header when :format isn't specifically defined in path_parameters.
  function format($force_format=null)
  {
    if ($force_format !== null) {
      return $this->format = $force_format;
    }
    elseif (!empty($this->format)) {
      return $this->format;
    }
    elseif (!empty($this->path_parameters[':format'])) {
      return $this->path_parameters[':format'];
    }
    return 'html';
  }
  
  function method()
  {
    $method = isset($_REQUEST['_method']) ? $_REQUEST['_method'] : $_SERVER['REQUEST_METHOD'];
    return strtolower($method);
  }
  
  function protocol()
  {
    return isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
  }
  
  function is_ssl()
  {
    return isset($_SERVER['HTTPS']);
  }
  
  function host()
  {
    $host = explode(':', $_SERVER['HTTP_HOST']);
    return $host[0];
  }
  
  function port()
  {
    $host = explode(':', $_SERVER['HTTP_HOST']);
    return isset($host[1]) ? $host[1] : ($_SERVER['HTTPS'] ? 443 : 80);
  }
  
  function port_string()
  {
    $port = $this->port();
    return ($port == 80 or $port == 443) ? '' : ":$port";
  }
  
  function subdomains()
  {
    $parts = explode('.', $this->host());
    if (count($parts) > 2) {
      return array_slice($parts, 0, count($parts) - 2);
    }
    return array();
  }
  
  function path()
  {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  }
  
  function url()
  {
    return $this->protocol().$this->host().$this->port_string().$_SERVER['REQUEST_URI'];
  }
  
  function relative_url_root()
  {
    if (isset($_SERVER['REDIRECT_URI']))
    {
      $root = dirname($_SERVER['REDIRECT_URI']);
      if ($root != '/') {
        return $root;
      }
    }
    return '';
  }
  
  function path_parameters($params=null)
  {
    if ($params !== null) {
      $this->path_parameters = $params;
    }
    return $this->path_parameters;
  }
  
  function & parameters()
  {
    $params = array_merge($_GET, $_POST, $this->path_parameters);
    if (get_magic_quotes_gpc()) {
      $this->sanitize_magic_quotes($params);
    }
    return $params;
  }
  
  function raw_body()
  {
    if (!isset($_SERVER['RAW_POST_DATA'])) {
      $_SERVER['RAW_POST_DATA'] = file_get_contents('php://input');
    }
    return $_SERVER['RAW_POST_DATA'];
  }
  
  function is_xml_http_request()
  {
    return (isset($_SERVER['X-Requested-With']) and $_SERVER['X-Requested-With'] == 'XMLHttpRequest');
  }
  
  function remote_ip()
  {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
      return $_SERVER['HTTP_CLIENT_IP'];
    }
    return $_SERVER['REMOTE_ADDR'];
  }
  
  
  private function parse_headers()
  {
    $this->headers = array();
    foreach ($_SERVER as $name => $value)
    {
      if (strpos($name, 'HTTP_') === 0)
      {
        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
        $this->headers[$name] = $value;
      }
    }
  }
  
  private function parse_query_string()
  {
    if (empty($_SERVER['QUERY_STRING']))
    {
      $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
      parse_str($_SERVER['QUERY_STRING'], $_GET);
    }
  }
  
  # TODO: Parse multipart/form-data, as well as XML post data.
  private function parse_post_body()
  {
    switch($this->content_type())
    {
      case 'application/x-www-form-urlencoded':
        parse_str($this->raw_body(), $_POST);
      break;
      
      case 'multipart/form-data':
        // ...
      break;
      
      case 'application/xml': case 'text/xml':
        // ...
      break;
      
      case 'application/json':
        $_POST = json_decode($this->raw_body(), true);
      break;
    }
  }
  
  private function sanitize_magic_quotes($ary)
  {
    if (is_array($ary))
    {
	    foreach(array_keys($ary) as $k) {
		    $this->sanitize_magic_quotes($ary[$k]);
	    }
    }
    else {
	    $ary = stripslashes($ary);
    }
  }
}

?>
