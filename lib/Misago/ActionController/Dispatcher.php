<?php
namespace Misago\ActionController;

# Dispatches and processes a HTTP request.
# 
# For instance, your index.php may look like this:
# 
#   <\?php
#   require_once dirname(__FILE__).'/config/boot.php';
#   ActionController_Dispatcher::dispatch();
#   ?\>
# 
class Dispatcher extends \Misago\Object
{
  protected $request;
  protected $response;
  
  function __construct($request=null, $response=null)
  {
    $this->request  = ($request  !== null) ? $request  : new CgiRequest();
    $this->response = ($response !== null) ? $response : new AbstractResponse();
  }
  
  static function dispatch()
  {
    $dispatcher = new self();
    $dispatcher->handle_request();
  }
  
  # IMPROVE: Parse out methods from ActionController\Base (and above): they're not actions.
  protected function handle_request()
  {
    $controller = Routing\Routes::recognize($this->request);
    $params = $this->request->path_parameters();
    
    if (!method_exists($controller, $params[':action'])) {
      throw new \Misago\Exception("No such action: ".get_class($controller)."::{$params[':action']}", 404);
    }
    if ($params[':action'] == 'process' or !is_callable(array($controller, $params[':action']))) {
      throw new \Misago\Exception("Tried to call a private/protected method as a public action: {$params[':action']}", 400);      
    }
    
    $controller->process($this->request, $this->response);
    $this->response->send();
  }
}

?>
