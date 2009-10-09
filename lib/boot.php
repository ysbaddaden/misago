<?php

ini_set('include_path',
	ROOT.DS.'app'.DS.'models'.PATH_SEPARATOR.
	ROOT.DS.'app'.DS.'controllers'.PATH_SEPARATOR.
	ROOT.DS.'app'.DS.'helpers'.PATH_SEPARATOR.
	ROOT.DS.'lib'.PATH_SEPARATOR.
	MISAGO.DS.'lib'.DS.'action_view'.DS.'helpers'.PATH_SEPARATOR.
	MISAGO.DS.'lib'.PATH_SEPARATOR.
	MISAGO.DS.'vendor'.PATH_SEPARATOR.
	ini_get('include_path').PATH_SEPARATOR
);

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'development';
}

require 'error_handlers.php';
require 'active_support'.DS.'additions.php';
require 'active_support'.DS.'string.php';
require 'active_support'.DS.'array.php';
require 'active_support'.DS.'active_array.php';
require 'active_support'.DS.'time.php';

if (!function_exists('apc_store')) {
  require 'fake_apc.php';
}
require 'cfg.php';
require 'misago_log.php';
require 'application.php';

require 'action_controller'.DS.'routing.php';

require ROOT.DS.'config'.DS.'environments'.DS."{$_SERVER['MISAGO_ENV']}.php";
require ROOT.DS.'config'.DS.'environment.php';

I18n::initialize();


function __autoload($class)
{
  $path = str_replace('_', DS, $class);
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

?>
