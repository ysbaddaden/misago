<?php

# Checks wether a variable is blank or not.
# 
# :namespace: Misago\ActiveSupport
function is_blank($var)
{
  if (empty($var)) {
    return (strlen($var) == 0);
  }
  $tmp = trim($var);
  return empty($tmp);
}

# Similar to http://php.net/scandir but recursive.
# :namespace: Misago\ActiveSupport
function & scandir_recursive($path, $sorting_order=0)
{
  $data = array();
  _scandir_recursive($path, $sorting_order, $data);
  
  # removes initial path
  /*
  $len    = strlen($path);
  $substr = function(&$path) use($len) { $path = substr($path, $len); }
  $data   = array_map($substr, $data);
  */
  $len = strlen($path);
  foreach(array_keys($data) as $i) {
    $data[$i] = substr($data[$i], $len);
  }
  
  ($sorting_order === 0) ? sort($data) : rsort($data);
  return $data;
}

# :nodoc:
function _scandir_recursive($path, $sorting_order, &$data)
{
  $dh = opendir($path);
  if ($dh)
  {
    while(($file = readdir($dh)) !== false)
    {
      if ($file == '.' or $file == '..') continue;
      
      $pathfile = "$path/$file";
      if (is_dir($pathfile))
      {
        $data[] = $pathfile;
        _scandir_recursive($pathfile, $sorting_order, $data);
      }
      elseif (is_file($pathfile)) {
        $data[] = $pathfile;
      }
    }
    closedir($dh);
  }
}

# Similar to http://php.net/rmdir but recursive.
# :namespace: Misago\ActiveSupport
function rmdir_recursive($path)
{
  $files = scandir_recursive($path, 1);
  foreach($files as $file)
  {
    $pathfile = "$path/$file";
    if (is_dir($pathfile)) {
      rmdir($pathfile);
    }
    else {
      unlink($pathfile);
    }
  }
  
  if (is_dir($path)) {
    rmdir($path);
  }
  else {
    unlink($path);
  }
}

# Similar to http://php.net/copy but recursive.
# :namespace: Misago\ActiveSupport
function copy_recursive($source, $dest)
{
  $files = scandir_recursive($source, 0);
  foreach($files as $file)
  {
    if (is_dir("$source/$file"))
    {
      if (!file_exists("$dest/$file")) {
        mkdir("$dest/$file", 0775, true);
      }
    }
    elseif (is_file("$source/$file")) {
      copy("$source/$file", "$dest/$file");
    }
  }
}

?>
