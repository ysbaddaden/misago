<?php

# PHP 5.2
define('ROOT', dirname(dirname(__FILE__)));

# PHP 5.3
#define('ROOT', dirname(__DIR__));

define('APP',    ROOT.'/app');
define('PUBLIC', ROOT.'/public');
define('TMP',    ROOT.'/tmp');

# symlink
define('MISAGO', ROOT.'/lib/misago');

# ext/phar 
#define('MISAGO', 'phar://misago.phar');
#require ROOT.'/lib/misago.phar';

require MISAGO.'/lib/boot.php';

?>
