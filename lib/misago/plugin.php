<?php

class Misago_Plugin
{
  static function & list_plugins()
  {
    $plugins = array();
    if (file_exists(ROOT."/vendor/plugins"))
    {
      $d = dir(ROOT."/vendor/plugins");
      while(($f = $d->read()) !== false) {
        $plugins[] = $f;
      }
      $d->close();
    }
    return $plugins;
  }
  
  static function include_path()
  {
    $path = apc_fetch(TMP."/plugin_lib_paths");
    if ($path === false)
    {
      $plugins = self::list_plugins();
      foreach($plugins as $i => $plugin) {
        $plugins[$i] = ROOT."/vendor/plugins/$plugin/lib";
      }
      $path = implode(PATH_SEPARATOR, $plugins);
      apc_store(TMP."/plugin_lib_paths", $path);
    }
    return $path;
  }
  
  static function install($url)
  {
    $name = basename($url);
    $path = ROOT."/vendor/plugins/$name";
    
    if (file_exists($path)) {
      die("Fatal error: plugin already exists in vendor/plugins/$name\n");
    }
    
    self::copy_plugin($url, $path);
    
#    if (file_exists("$path/install.php")) {
#      include "$path/install.php";
#    }
  }
  
  static function update($url)
  {
    $name = basename($name_or_url);
    $path = ROOT."/vendor/plugins/$name";
    if (!file_exists($path)) {
      die("Fatal error: no such plugin $name\n");
    }
    self::copy_plugin($url, $path);
  }
  
  static function uninstall($name_or_url)
  {
    $name = basename($name_or_url);
    $path = ROOT."/vendor/plugins/$name";
    if (!file_exists($path)) {
      die("Skipped unknown plugin $name.");
    }
    
#    if (file_exists("$path/uninstall.php")) {
#      include "$path/uninstall.php";
#    }
    
    rmdir_recursive($tmp);
  }
  
  private static function copy_plugin($source, $dest)
  {
    if (preg_match('/^git:\/\/|\.git$/', $source))
    {
      $type = 'git';
      $cmd  = "git clone $source";
    }
    elseif (preg_match('/^(svn|http|https):\/\//'))
    {
      $type = 'svn';
      $cmd  = "svn checkout $source";
    }
    
    if ($type == 'svn' or $type == 'git')
    {
      $tmp = TMP.'/plugins/'.basename($dest);
      if (!file_exists($tmp)) {
        mkdir($tmp, 0775, true);
      }
      
      chdir($tmp);
      passthru($cmd);
      copy_recursive($tmp, $dest);
      rmdir_recursive($tmp);
    }
    else {    
      copy_recursive($source, $dest);
    }
  }
}

?>
