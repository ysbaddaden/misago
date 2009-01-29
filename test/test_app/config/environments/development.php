<?php

define('DEBUG', 2);
error_reporting(E_ALL);

define('PROTOCOL', 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : null));
define('FULL_BASE_URL', PROTOCOL.'://'.$_SERVER['HTTP_HOST']);

?>
