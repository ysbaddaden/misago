<?php

# Checks wether a string is a symbol or not.
# :namespace: ActiveSupport
function is_symbol($str)
{
  return (is_string($str) and strpos($str, ':') === 0);
}

?>
