<?php

# http://groups.google.com/group/php-standards/web/psr-0-final-proposal
function __autoload($origClassName)
{
  $className = ltrim($origClassName, '\\');
  $fileName  = '';
  $namespace = '';
  
  if ($lastNsPos = strripos($className, '\\'))
  {
    $namespace = substr($className, 0, $lastNsPos);
    $className = substr($className, $lastNsPos + 1);
    $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
  }
  $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';

  if (!include $fileName)
  {
    echo '<pre>';
    echo "\nOops. An error occured while loading $fileName\n";
    debug_print_backtrace();
    echo '</pre>';
    exit;
  }
  
  if (method_exists($origClassName, '__constructStatic')) {
    $origClassName::__constructStatic();
  }
}

# transforms all errors to exceptions (but keeps warnings and notices as is)
set_error_handler(function($errno, $errstr, $errfile, $errline) {
  throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}, E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);

# intial requirements
if (!function_exists('apc_store')) {
  require __DIR__.'/fake_apc.php';
}
require __DIR__.'/Config.php';
require __DIR__.'/Plugin.php';

# include path
ini_set('include_path',
	ROOT.'/app/models'.PATH_SEPARATOR.
	ROOT.'/app/controllers'.PATH_SEPARATOR.
	ROOT.'/app/helpers'.PATH_SEPARATOR.
	ROOT.'/lib'.PATH_SEPARATOR.
	MISAGO.'/lib'.PATH_SEPARATOR.
	ROOT.'/vendor'.PATH_SEPARATOR.
	MISAGO.'/vendor'.PATH_SEPARATOR.
	Misago\Plugin::include_path().PATH_SEPARATOR.
	ini_get('include_path').PATH_SEPARATOR
);

# environment
if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'development';
}

# more requirements
require 'Misago/ActiveSupport.php';
require 'Misago/ActionController.php';
require ROOT.'/config/environment.php';
require ROOT."/config/environments/{$_SERVER['MISAGO_ENV']}.php";

Misago\ActionController\Routing\Routes::boot();
Misago\I18n::initialize();

?>
