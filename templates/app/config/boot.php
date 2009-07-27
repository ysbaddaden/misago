<?php

define('ROOT', dirname(dirname(__FILE__)));
define('APP', ROOT.'/app');
define('MISAGO', ROOT.'/lib/misago');
define('PUBLIC', ROOT.'/public');
define('TMP', ROOT.'/tmp');
 
# or if you are using PHAR:
#define('ROOT', dirname(dirname(__FILE__)));
#define('APP', ROOT.'/app');
#define('MISAGO', 'phar://misago.phar');
#define('PUBLIC', ROOT.'/public');
#define('TMP', ROOT.'/tmp');

#require ROOT.'/lib/misago.phar';
require MISAGO.'/lib/boot.php';

?>
