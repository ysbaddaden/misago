<?php

define('DEBUG', 1);
error_reporting(E_ALL | E_STRICT);

cfg_set('action_controller.base_url', 'http://localhost:3009');
cfg_set('action_mailer.delivery_method', 'test');

# disables CSRF protection
cfg_set('action_controller.allow_forgery_protection', false);

?>
