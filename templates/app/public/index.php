<?php
require dirname(__FILE__).DS.'..'.DS.'config'.DS.'boot.php';

try {
  ActionController_Dispatcher::dispatch();
}
catch(MisagoException $e) {
  $e->render();
}
?>
