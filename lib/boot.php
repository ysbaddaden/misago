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

if (!isset($_ENV['MISAGO_DEBUG'])) {
  $_ENV['MISAGO_DEBUG'] = 0;
}
if (!isset($_ENV['MISAGO_ENV'])) {
  $_ENV['MISAGO_ENV'] = 'development';
}

#require 'object.php';
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
require 'http.php';
require 'application.php';

require ROOT."/config/environments/{$_ENV['MISAGO_ENV']}.php";
require ROOT.'/config/environment.php';

require 'i18n.php';
I18n::startup();

require 'action_controller/functions.php';
require ROOT.'/config/routes.php';
ActionController_host_analyzer();


function __autoload($class)
{
  $path = str_replace('_', '/', $class);
  $path = String::underscore($path);
  
  if (!include "$path.php")
  {
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
