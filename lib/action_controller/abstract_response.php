<?php

# Response data.
# 
# This is some Misaso internal, response should never be used directly.
# You should use methods from <tt>ActionController_Base</tt> instead.
# 
class ActionController_AbstractResponse extends Misago_Object
{
  public $headers = array(
    'Status'       => 200,
    'Content-Type' => 'text/html',
  );
  public $body = '';
  
  # For the list of HTTP status code, and explanations, see:
  # http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
  private $statuses = array(
    100 => 'Continue',
    101 => 'Switching Protocols',
    
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Content',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
#   306 => '',
    307 => 'Temporary Redirect',
    
    400 => 'Bad Request',
    401 => 'Unauthorized',
#   402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Request Range Not Satisfiable',
    417 => 'Expectation Failed',
    
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
  );
  
  private $mimetypes = array(
    'text' => 'text/plain',
    'html' => 'text/html',
    'js'   => 'text/javascript',
    'css'  => 'text/css',
    'ics'  => 'text/calendar',
    'csv'  => 'text/csv',
    'xml'  => 'application/xml',
    'rss'  => 'application/rss+xml',
    'atom' => 'application/atom+xml',
    'yaml' => 'application/x-yaml',
    'json' => 'application/json',
  );
  
  function content_type($content_type=null)
  {
    if ($content_type !== null) {
      $this->headers['Content-Type'] = $content_type;
    }
    return $this->headers['Content-Type'];
  }
  
  function content_type_from_format($format)
  {
    $format = isset($this->mimetypes[$format]) ? $this->mimetypes[$format] : 'text/html';
    return $this->headers['Content-Type'] = $format;
  }
  
  function status($status=null)
  {
    if ($status !== null) {
      $this->headers['Status'] = $status;
    }
    return $this->statuses[$this->headers['Status']];
  }
  
  function redirect($location, $status=302)
  {
    $this->status($status);
    $this->headers['Location'] = $location;
    $this->send();
  }
  
  function send()
  {
    # HTTP headers
    if (!headers_sent())
    {
      foreach($this->headers as $k => $v) {
        header("$k: $v", true);
      }
    }
    
    # HTTP body
    echo $this->body;
  }
}

?>
