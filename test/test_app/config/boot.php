<?php

define('ROOT',   dirname(dirname(__FILE__)));
define('APP',    ROOT.'/app');
define('MISAGO', ROOT.'/../..');
define('PUBLIC', ROOT.'/public');
define('TMP',    ROOT.'/tmp');

require MISAGO.'/lib/boot.php';

# forces an environment if none is declared
if (!isset($_ENV['environment'])) {
  $_ENV['environment'] = 'development';
}

require ROOT."/config/environments/{$_ENV['environment']}.php";
require ROOT.'/config/environment.php';

require 'action_controller/dispatcher.php';
require ROOT.'/config/routes.php';
?>
