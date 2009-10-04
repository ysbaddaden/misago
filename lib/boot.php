<?php

ini_set('include_path',
	ROOT.'/app/models'.PATH_SEPARATOR.
	ROOT.'/app/controllers'.PATH_SEPARATOR.
	ROOT.'/app/helpers'.PATH_SEPARATOR.
	ROOT.'/lib'.PATH_SEPARATOR.
	MISAGO.'/lib/action_view/helpers'.PATH_SEPARATOR.
	MISAGO.'/lib'.PATH_SEPARATOR.
	MISAGO.'/vendor'.PATH_SEPARATOR.
	ini_get('include_path').PATH_SEPARATOR
);

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'development';
}

require 'error_handlers.php';
require 'active_support/additions.php';
require 'active_support/string.php';
require 'active_support/array.php';
require 'active_support/active_array.php';
require 'active_support/time.php';

if (!function_exists('apc_store')) {
  require 'fake_apc.php';
}
require 'cfg.php';
require 'misago_log.php';
require 'application.php';

parse_query_string();
if ($_SERVER['HTTP_METHOD'] == 'PUT') {
  parse_post_body();  
}

require ROOT."/config/environments/{$_SERVER['MISAGO_ENV']}.php";
require ROOT.'/config/environment.php';

require 'i18n.php';
I18n::startup();

require ROOT.'/config/routes.php';


function __autoload($class)
{
  $path = str_replace('_', '/', $class);
  $path = String::underscore($path);
  
  if (!include "$path.php")
  {
    echo "\nError: an error occured while loading $path.php\n";
    debug_print_backtrace();
    exit;
  }
}

function sanitize_magic_quotes(&$params)
{
  if (is_array($params))
  {
	  foreach(array_keys($params) as $k) {
		  sanitize_magic_quotes($params[$k]);
	  }
  }
  else {
	  $params = stripslashes($params);
  }
}

function parse_query_string()
{
  if (empty($_SERVER['QUERY_STRING']))
  {
    $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    parse_str($_SERVER['QUERY_STRING'], $_GET);
  }
}
  
# TODO: Parse multipart/form-data.
# TODO: Parse incoming XML.
function parse_post_body()
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

?>
