<?php

# Dispatches and processes a HTTP request.
# 
# For instance, your index.php may look like this:
# 
#   <\?php
#   require_once dirname(__FILE__).'/config/boot.php';
#   ActionController_Dispatcher::dispatch();
#   ?\>
# 
class ActionController_Dispatcher extends Object
{
  protected $request;
  protected $response;
  
  function __construct($request=null, $response=null)
  {
    $this->request  = ($request  !== null) ? $request  : new ActionController_CgiRequest();
    $this->response = ($response !== null) ? $response : new ActionController_AbstractResponse();
  }
  
  static function dispatch()
  {
    $dispatcher = new self();
    $dispatcher->handle_request();
  }
  
  # TODO: Parse out methods from ActionController_Base (and above): they're not actions for sure.
  protected function handle_request()
  {
    $controller = ActionController_Routing::recognize($this->request);
    $params = $this->request->path_parameters();
    
    if (!method_exists($controller, $params[':action'])) {
      throw new MisagoException("No such action: ".get_class($controller)."->{$params[':action']}", 404);
    }
    if ($params[':action'] == 'process' or !is_callable(array($controller, $params[':action']))) {
      throw new MisagoException("Tried to call a private/protected method as a public action: {$params[':action']}", 400);      
    }
    
    $controller->process($this->request, $this->response);
  }
}

?>
