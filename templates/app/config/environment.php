<?php

# charsets
ini_set('default_charset', 'UTF-8');

mb_language('uni');
mb_internal_encoding('UTF-8');
if (extension_loaded('mbstring')) {
  ini_set('mbstring.func_overload', 7);
}

# languages
cfg::set('i18n_default_locale', 'en');
setlocale(LC_ALL, 'en_US.UTF-8');

# date & tz
date_default_timezone_set('UTC');

# mailer
# cfg::set('mailer_perform_deliveries', false);
# cfg::set('delivery_method', 'sendmail');
# cfg::set('mailer_return_path', 'postmaster@domain.com');
# cfg::set('mailer_default_from', 'me <contact@domain.com>');

Session::start();

?>
