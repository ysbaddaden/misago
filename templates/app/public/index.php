<?php

require dirname(__FILE__).'/../config/boot.php';

/// TODO Analyze host in order to forge URL
#$host = ActionController_analyse_host();

try {
  ActionController_dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
}
catch(MisagoException $e)
{
  $e->render();
}
?>
