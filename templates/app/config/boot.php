<?php

define('ROOT', (DIRECTORY_SEPARATOR == '/') ? dirname(dirname(__FILE__)) :
  str_replace(' ', '\ ', str_replace(DIRECTORY_SEPARATOR, '/', dirname(dirname(__FILE__)))));

define('APP',    ROOT.'/app');
define('PUBLIC', ROOT.'/public');
define('TMP',    ROOT.'/tmp');

define('MISAGO', ROOT.'/lib/misago');

# If you are using ext/phar:
#define('MISAGO', 'phar://misago.phar');
#require ROOT.'/lib/misago.phar';

require MISAGO.'/lib/boot.php';

?>
