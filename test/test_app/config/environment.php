<?php
ini_set('default_charset', 'UTF-8');

mb_language('uni');
mb_internal_encoding('UTF-8');
if (extension_loaded('mbstring')) {
	ini_set('mbstring.func_overload', 7);
}

cfg_set('i18n.default_locale', 'en');
setlocale(LC_ALL, 'en_US.UTF-8');

# date & tz
date_default_timezone_set('UTC');

cfg_set('action_mailer.perform_deliveries', false);
#cfg_set('action_mailer.delivery_method', 'sendmail');
#cfg_set('action_mailer.return_path', 'postmaster@domain.com');

cfg_set('action_controller.cache_store', 'memory_store');
#cfg_set('action_controller.allow_forgery_protection', true);
?>
