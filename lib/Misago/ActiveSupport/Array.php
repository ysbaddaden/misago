<?php

# Checks wether an array is a hash of key/value pairs or just a list of values.
# :namespace: Misago\ActiveSupport\Array
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

# Simplifies handling of collections.
# 
#   $collection = array_collection('a,b,  c, d');
#   $collection = array_collection(array('a', ' b', 'c '));
# 
# :namespace: Misago\ActiveSupport\Array
function & array_collection($collection)
{
  if (!is_array($collection)) {
    $collection = explode(',', $collection);
  }
  $_collection = array();
  foreach($collection as $item)
  {
    $item = trim($item);
    if (!empty($item)) {
      $_collection[] = $item;
    }
  }
  return $_collection;
}

# Recursively merges hashes, overwriting non array/hash values.
# :namespace: Misago\ActiveSupport\Array
function & hash_merge_recursive()
{
  $hashes = func_get_args();
  $hash   = array_shift($hashes);
  foreach($hashes as $k => $h)
  {
    foreach($h as $k => $v)
    {
      if (isset($hash[$k]) and is_hash($v)) {
        $hash[$k] = hash_merge_recursive($hash[$k], $v);
      }
      else {
        $hash[$k] = $v;
      }
    }
  }
  return $hash;
}

# Recursively sorts arrays (skipping hashes).
# :namespace: Misago\ActiveSupport\Array
function array_sort_recursive(&$ary)
{
  sort($ary);
  foreach($ary as $k => $v)
  {
    if (is_array($v) and !is_hash($v)) {
      array_sort_recursive($ary[$k]);
    }
  }
}

/*
# Transforms an array with mixed keys (array and hash) into a full hash one.
# 
# Example:
#  
#   $includes = array('tags', 'comments' => array('order' => 'created_at asc'));
#   $includes = linearize_options_tree($includes);
# 
# $includes will be:
# 
#   array(
#     'tags' => array(),
#     'comments' => array('order' => 'created_at asc'),
#   );
# 
# :namespace: Misago\ActiveSupport\Array
function linearize_option_tree($ary)
{
  $h = array();
  foreach($ary as $k => $v)
  {
    if (is_int($k)) {
      $h[$v] = array();
    }
    else {
      $h[$k] = is_array($v) ? linearize_options_tree($v) : $v;
    }
  }
  return $h;
}
*/
?>
