<?php
define('DS',     DIRECTORY_SEPARATOR);
define('ROOT',   dirname(dirname(__FILE__)));
define('APP',    ROOT.DS.'app');
define('MISAGO', ROOT.DS.'..'.DS.'..');
define('PUBLIC', ROOT.DS.'public');
define('TMP',    ROOT.DS.'tmp');

require MISAGO.DS.'lib'.DS.'boot.php';
?>
