<?php

# Using LightTPD 404-handler, or using non GET/POST HTTP requests,
# $_GET and $_POST aren't always populated by PHP. Let's fix that.
if (empty($_SERVER['QUERY_STRING']))
{
  $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
  parse_str($_SERVER['QUERY_STRING'], $_GET);
}
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{
  switch($_SERVER['CONTENT_TYPE'])
  {
    case 'application/x-www-form-urlencoded':
      parse_str(file_get_contents('php://input'), $_POST);
    break;
    
    case 'multipart/form-data':
      // ...
    break;
  }
}
$_REQUEST = array_merge($_GET, $_POST);

# boots up framework
require dirname(__FILE__).'/../config/boot.php';

# dispatches request
try
{
  $method = isset($_POST['_method']) ? strtoupper($_POST['_method']) : $_SERVER['REQUEST_METHOD'];
  $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  ActionController_dispatch($method, $uri);
}
catch(MisagoException $e) {
  $e->render();
}

?>
