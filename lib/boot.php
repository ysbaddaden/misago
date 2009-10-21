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
require 'active_support/active_support.php';
require 'action_controller/action_controller.php';

if (!function_exists('apc_store')) {
  require 'fake_apc.php';
}
require 'misago_log.php';

require ROOT."/config/environments/{$_SERVER['MISAGO_ENV']}.php";
require ROOT.'/config/environment.php';

I18n::initialize();


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

?>
