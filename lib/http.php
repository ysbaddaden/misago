<?php
# For the list of HTTP status code, and explanations, see:
# http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html

class HTTP
{
  static $codes = array(
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
  
  /**
   * Sets status in output HTTP header.
   */
  static function status($code=200)
  {
    $status = self::$codes[$code];
    header("Status: $code", true);
  }
  
  /**
   * Redirects current request.
   */
  static function redirect($url, $code=null)
  {
    if (!DEBUG)
    {
      if ($code) {
        self::status($code);
      }
      header("Location: $url");
    }
    elseif ($code) {
      echo "<p><a href=\"$url\">$url</a> [status: $code {self::$codes[$code]}]</p>";
    }
    else {
      echo "<p><a href=\"$url\">$url</a> [status: 302 Found]</p>";
    }
    exit;
  }
}

?>
