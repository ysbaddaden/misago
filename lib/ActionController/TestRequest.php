<?php
# :nodoc: all
class ActionController_TestRequest extends Misago_Object implements ActionController_AbstractRequest
{
  public  $headers = array();
  
  private $get;
  private $post;
  private $data    = array(
    'protocol'     => 'http://',
    'host'         => 'localhost',
    'port'         => '3009',
    'content_type' => 'text/html',
    ':method'      => 'GET',
    ':action'      => 'index',
    ':format'      => 'html',
  );
  private $format;
  private $path_parameters = array();
  
  function __construct($data, $get=array(), $post=array())
  {
    $this->data = array_merge($this->data, $data);
    $this->get  = $get;
    $this->post = $post;
  }
  
  function accepts()
  {
    
  }
  
  function content_type()
  {
    return $this->data['content_type'];
  }
  
  function format($force_format=null)
  {
    return $this->data[':format'];
  }
  
  function method()
  {
    return strtolower($this->data[':method']);
  }
  
  function protocol()
  {
    return $this->data['protocol'];
  }
  
  function is_ssl()
  {
    return ($this->data['protocol'] == 'https://');
  }
  
  function host()
  {
    return $this->data['host'];
  }
  
  function port()
  {
    return $this->data['port'];
  }
  
  function port_string()
  {
    $port = $this->port();
    return ($port == 80 or $port == 443) ? '' : ":$port";
  }
  
  function subdomains()
  {
    return array();
  }
  
  function path()
  {
    return isset($this->data['path']) ? $this->data['path'] :
      (string)url_for($this->path_parameters());
  }
  
  function url()
  {
    if (isset($this->data['url'])) {
      return $this->data['url'];
    }
    return url_for(array_merge($this->path_parameters, $this->get));
  }
  
  function relative_url_root()
  {
    return '';
  }
  
  function path_parameters($params=null)
  {
    return array_intersect_key($this->data, array(
      ':method'     => '',
      ':controller' => '',
      ':action'     => '',
      ':id'         => '',
      ':format'     => '',
    ));
  }
  
  function & parameters()
  {
    $params = array_merge($this->get, $this->post, $this->path_parameters());
    return $params;
  }
  
  function raw_body()
  {
    return '';
  }
  
  function is_xml_http_request()
  {
    return (bool)$this->data['xhr'];
  }
  
  function remote_ip()
  {
    return '127.0.0.1';
  }
}

?>
