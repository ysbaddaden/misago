#! /usr/bin/php -d phar.readonly=0
<?php

parse_dir(__DIR__.'/../lib', $files);
parse_dir(__DIR__.'/../vendor', $files);
sort($files);

$phar = new Phar(__DIR__.'/misago.phar', 0, 'misago.phar');
foreach($files as $file)
{
  $local_file = str_replace(__DIR__.'/../', "", $file);
  echo "$local_file\n";
  $phar->addFile($file, $local_file);
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
