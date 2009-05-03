<?php
/**
 * Extensions for arrays.
 * 
 * @package ActiveSupport
 */

/**
 * Checks wether an array is a hash of key/value pairs or just a list of values.
 */
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

/**
 * Simplifies handling of collections.
 * 
 * Examples:
 *   - $collection = array_collection('a,b,  c, d');
 *   - $collection = array_collection(array('a', ' b', 'c '));
 * 
 * @return Array
 * @param $collection Mixed.
 */
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

/**
 * Recursively merges hashes, overwriting non array/hash values.
 */
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

/**
 * Recursively sorts arrays (skipping hashes).
 */
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

?>
