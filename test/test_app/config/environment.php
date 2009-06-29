<?php

# charsets
ini_set('default_charset', 'UTF-8');

mb_language('uni');
mb_internal_encoding('UTF-8');
if (extension_loaded('mbstring')) {
	ini_set('mbstring.func_overload', 7);
}

date_default_timezone_set('UTC');

# mailer
cfg::set('mailer_perform_deliveries', false);
# cfg::set('delivery_method', 'sendmail');
# cfg::set('mailer_return_path', 'postmaster@domain.com');

?>
