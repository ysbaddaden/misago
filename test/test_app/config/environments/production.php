<?php

define('DEBUG', 0);
error_reporting(0);

define('PROTOCOL', 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : null));
define('FULL_BASE_URL', PROTOCOL.'://'.$_SERVER['HTTP_HOST']);

# gzip output
ob_start("ob_gzhandler");

?>
