#! /usr/bin/php -d phar.readonly=0
<?php
# DEPRECATED: use 'pake phar' instead.

parse_dir(dirname(__FILE__).'/../lib', $files);
parse_dir(dirname(__FILE__).'/../vendor', $files);
parse_dir(dirname(__FILE__).'/../templates', $files);
sort($files);

$phar = new Phar(dirname(__FILE__).'/misago.phar', 0, 'misago.phar');
foreach($files as $file)
{
  $local_file = str_replace(dirname(__FILE__).'/../', "", $file);
  $phar->addFile($file, $local_file);
#  echo "$local_file\n";
}
$phar->setStub('<?php __HALT_COMPILER(); ?>');
$phar->stopBuffering();

function parse_dir($path, &$files)
{
  $dh = opendir($path);
  while(($file = readdir($dh)) !== false)
  {
    if ($file[0] == '.') {
      continue;
    }
    $file = "$path/$file";
    
    if (is_file($file)) {
      $files[] = $file;
    }
    elseif (is_dir($file)) {
      parse_dir($file, $files);
    }
  }
  closedir($dh);
}

?>
