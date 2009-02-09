<?php
# IMPROVE: Add possibility to run a specific migration (version = XXX).

$direction = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'up';
$from_version = ActiveRecord_Migration::get_version();


$migration_files = glob(ROOT.'/db/migrate/*.php');
sort($migration_files);

$runned_migrations = 0;

foreach($migration_files as $file)
{
  $file = str_replace(ROOT.'/db/migrate/', '', $file);
  preg_match("/^([\d]+)_([\w_]+)\.php$/", $file, $match);
  
  $ts = $match[1];
  if ($ts > $from_version)
  {
    $runned_migrations += 1;
    
    require ROOT.'/db/migrate/'.$file;
    $class = String::singularize(String::camelize($match[2]));
    $migration = new $class($ts, $_ENV['MISAGO_ENV']);
    $result = $migration->migrate($direction);
    
    if ($result) {
       ActiveRecord_Migration::save_version($ts);
    }
    else {
      die();
    }
  }
}

if ($runned_migrations === 0) {
  echo "Database is up to date.\n";
}

?>
