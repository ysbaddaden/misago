<?php

define('ROOT',   dirname(dirname(__FILE__)));
define('APP',    ROOT.'/app');
define('MISAGO', ROOT.'/lib/misago');
define('PUBLIC', ROOT.'/public');
define('TMP',    ROOT.'/tmp');

require MISAGO.'/lib/boot.php';

# forces an environment if none is declared
if (!isset($_ENV['MISAGO_ENV'])) {
  $_ENV['MISAGO_ENV'] = empty($_ENV['environment']) ? 'development' : $_ENV['environment'];
}

require ROOT."/config/environments/{$_ENV['MISAGO_ENV']}.php";
require ROOT.'/config/environment.php';

require 'action_controller/dispatcher.php';
require ROOT.'/config/routes.php';

?>
