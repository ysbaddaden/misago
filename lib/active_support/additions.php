<?php

# @namespace ActiveSupport
function is_blank($var)
{
  if (empty($var)) {
    return (strlen($var) == 0);
  }
  $tmp = trim($var);
  return empty($tmp);
}

?>
