<?php

define('DEBUG', 1);
error_reporting(E_STRICT);

if (isset($_SERVER['HTTP_HOST']))
{
  define('PROTOCOL', 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : null));
  define('FULL_BASE_URL', PROTOCOL.'://'.$_SERVER['HTTP_HOST']);
}

?>
