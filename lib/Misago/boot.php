<?php

# http://groups.google.com/group/php-standards/web/psr-0-final-proposal
function __autoload($className)
{
  $className = ltrim($className, '\\');
  $fileName  = '';
  $namespace = '';
  
  if ($lastNsPos = strripos($className, '\\'))
  {
    $namespace = substr($className, 0, $lastNsPos);
    $className = substr($className, $lastNsPos + 1);
    $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
  }
  $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
#  $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';
  
  if (!require $fileName)
  {
    echo "\nOops. An error occured while loading $path.php\n";
    debug_print_backtrace();
    exit;
  }
}

require __DIR__.'/cfg.php';

if (!function_exists('apc_store')) {
  require __DIR__.'/fake_apc.php';
}

ini_set('include_path',
	ROOT.'/app/models'.PATH_SEPARATOR.
	ROOT.'/app/controllers'.PATH_SEPARATOR.
	ROOT.'/app/helpers'.PATH_SEPARATOR.
	ROOT.'/lib'.PATH_SEPARATOR.
	MISAGO.'/lib'.PATH_SEPARATOR.
	MISAGO.'/vendor'.PATH_SEPARATOR.
	Misago_Plugin::include_path().PATH_SEPARATOR.
	ini_get('include_path').PATH_SEPARATOR
);

if (!isset($_SERVER['MISAGO_ENV'])) {
  $_SERVER['MISAGO_ENV'] = 'development';
}

require 'Misago/ActiveSupport.php';
require 'Misago/ActionController.php';
require ROOT."/config/environments/{$_SERVER['MISAGO_ENV']}.php";
require ROOT.'/config/environment.php';

\Misago\I18n::initialize();

?>
