<?php

$location = dirname(__FILE__).'/../../../..';
$_ENV['MISAGO_ENV'] = 'test';

require_once "$location/test/test_app/config/boot.php";
require_once MISAGO."/lib/action_view/helpers/html_tag.php";
require_once MISAGO."/lib/action_view/helpers/form_tag.php";
require_once MISAGO."/lib/action_view/helpers/form.php";

class Test_ActionView_Helper_FormHelper extends Unit_Test
{
  
}

new Test_ActionView_Helper_FormHelper();

?>
