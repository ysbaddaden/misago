<?php

# PATHS
define('ROOT',   dirname(__DIR__));
define('PUBLIC', ROOT.'/public');
define('TMP',    ROOT.'/tmp');

# MISAGO
define('MISAGO', ROOT.'/lib/misago');
#define('MISAGO', 'phar://misago.phar/');
#include ROOT.'/lib/misago.phar';

# APP
define('APP', ROOT.'/app');
#define('APP', 'phar://app.phar/');
#include ROOT.'/lib/app.phar';

require MISAGO.'/lib/boot.php';

?>
