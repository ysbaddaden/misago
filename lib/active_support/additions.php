<?php

# Checks wether a variable is blank or not.
# 
# :namespace: ActiveSupport
function is_blank($var)
{
  if (empty($var)) {
    return (strlen($var) == 0);
  }
  $tmp = trim($var);
  return empty($tmp);
}

/*
# Similar to array, except that userdata is the same for all values.
function m_array_map($ary, $callback, $userdata=null)
{
  $args = funct_get_args();
  $args = array_slice($args, 1);
  foreach(array_keys($ary) as $i)
  {
    $args[0] = $ary[$i];
    $ary[$i] = call_user_func_array($callback, $args);
  }
  return $ary;
}
*/

# Similar to http://php.net/scandir but recursive.
function & scandir_recursive($path, $sorting_order=0, $context=null)
{
  $data = array();
  _scandir_recursive($path, $sorting_order, $context, $data);
  
  # removes initial path
  #$data = m_array_map('substr', $data, strlen($path));
  $len = strlen($path);
  foreach(array_keys($data) as $i) {
    $data[$i] = substr($data[$i], $len);
  }
  
  ($sorting_order === 0) ? sort($data) : rsort($data);
  return $data;
}

# :nodoc:
function _scandir_recursive($path, $sorting_order, $context, &$data)
{
  $dh = opendir($path, $context);
  if ($dh)
  {
    while(($file = readdir($dh)) !== false)
    {
      if ($file == '.' or $file == '..') continue;
      
      $pathfile = "$path/$file";
      if (is_dir($pathfile))
      {
        $data[] = $pathfile;
        _scandir_recursive($_path, $sorting_order, $context, $data);
      }
      elseif (is_file($pathfile)) {
        $data[] = $pathfile;
      }
    }
    closedir($dh);
  }
}

# Similar to http://php.net/rmdir but recursive.
function rmdir_recursive($path, $context=null)
{
  $files = scandir_recursive($path, 1, $context);
  foreach($files as $file)
  {
    $pathfile = "$path/$file";
    if (is_dir($pathfile)) {
      rmdir($pathfile, $context);
    }
    else {
      unlink($pathfile, $context);
    }
  }
}

# Similar to http://php.net/copy but recursive.
function copy_recursive($source, $dest, $context=null)
{
  $files = scandir_recursive($source, 0, $context);
  foreach($files as $file)
  {
    if (is_dir("$source/$file")) {
      mkdir("$dest/$file", 0775, true, $context);
    }
    elseif (is_file("$source/$file")) {
      copy("$source/$file", "$dest/$file", $context);
    }
  }
}

?>
