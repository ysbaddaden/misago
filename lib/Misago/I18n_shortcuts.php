<?php

# Shortcut for <tt>Misago\I18n::translate()</tt>.
# 
# Examples:
# 
#   t($string, $context);
#   t($string, array('context' => $context));
#   t("foor {{bar}}", array('context' => $context, 'bar' => 'baz'));
# 
function t($str, $options=null)
{
  if (!is_array($options) and !empty($options)) {
    $options = array('context' => $options);
  }
  return Misago\I18n::translate($str, $options);
}

# Shortcut for <tt>Misago\I18n::localize()</tt>.
function l($obj, $options=array())
{
  return Misago\I18n::localize($obj, $options);
}

?>
