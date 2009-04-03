<?php

require dirname(__FILE__).'/../config/boot.php';

# TODO: Analyze host to permit creation of URL
#$host = ActionController_analyse_host();

try
{
  ActionController_dispatch(
    isset($_POST['_method']) ? strtoupper($_POST['_method']) : $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
  );
}
catch(MisagoException $e) {
  $e->render();
}
?>
