<?php

ini_set('include_path',
	ROOT.'/app/models'.PATH_SEPARATOR.
	ROOT.'/app/helpers'.PATH_SEPARATOR.
	ROOT.'/app/controllers'.PATH_SEPARATOR.
	ROOT.'/lib'.PATH_SEPARATOR.
	MISAGO.'/lib'.PATH_SEPARATOR.
	MISAGO.'/vendor'.PATH_SEPARATOR.
	ini_get('include_path').PATH_SEPARATOR
);

#require 'object.php';
require 'active_support/string.php';
require 'active_support/array.php';
require 'active_support/time.php';

require 'http.php';

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
