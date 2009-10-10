<?php
require dirname(__FILE__).'/../config/boot.php';

try {
  ActionController_Dispatcher::dispatch();
}
catch(MisagoException $e) {
  $e->render();
}
?>
