<?php

# Checks wether an array is a hash of key/value pairs or just a list of values. 
function is_hash($arr)
{
  if (!is_array($arr)) {
    return false;
  }
  
  foreach(array_keys($arr) as $k)
  {
    if (!is_int($k)) {
      return true;
    }
  }
  return false;
}

?>
