<?php

define('DS',     DIRECTORY_SEPARATOR);
define('ROOT',   dirname(dirname(__FILE__)));
define('APP',    ROOT.DS.'app');
define('MISAGO', ROOT.DS.'lib'.DS.'misago');
define('PUBLIC', ROOT.DS.'public');
define('TMP',    ROOT.DS.'tmp');
 
# or if you are using PHAR:
#define('ROOT',   dirname(dirname(__FILE__)));
#define('APP',    ROOT.DS.'app');
#define('MISAGO', 'phar://misago.phar');
#define('PUBLIC', ROOT.DS.'public');
#define('TMP',    ROOT.DS.'tmp');

#require ROOT.DS.'lib'.DS.'misago.phar';
require MISAGO.DS.'lib'.DS.'boot.php';

?>
