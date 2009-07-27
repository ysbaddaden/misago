<?php

require dirname(__FILE__).'/../config/boot.php';

try
{
  $method = isset($_POST['_method']) ? strtoupper($_POST['_method']) : $_SERVER['REQUEST_METHOD'];
  $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  
  # using the 404-handler QUERY_STRING is undefined, and $_GET isn't populated
  if (empty($_SERVER['QUERY_STRING']))
  {
    $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    parse_str($_SERVER['QUERY_STRING'], $_GET);
  }
  $_REQUEST = array_merge($_GET, $_POST);
  
  ActionController_dispatch($method, $uri);
}
catch(MisagoException $e) {
  $e->render();
}
?>
