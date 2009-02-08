<?php
# TODO Add possibility to run a specific migration (version = XXX).

$direction = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'up';


#$db = ActiveRecord_Connection::create($_ENV['MISAGO_ENV']);

# TODO: create the misago_information_schema table if it doesn't exists.
#$version = $db->select_value("SELECT timestamp
#  FROM misago_information_schema
#  ORDER BY timestamp DESC LIMIT 1 ;");

$migration_files = glob(ROOT.'/db/migrate/*.php');
foreach($migration_files as $file)
{
  $file = str_replace(ROOT.'/db/migrate/', '', $file);
  preg_match("/^([\d]+)_([\w_]+)\.php$/", $file, $match);
  
  $ts    = $match[1];
  $class = String::singularize(String::camelize($match[2]));
  
  require ROOT.'/db/migrate/'.$file;
  $migration = new $class($ts, $_ENV['MISAGO_ENV']);
  $result = $migration->migrate($direction);
  
  if ($result)
  {
    # TODO: Record timestamp
    # $db->update('misago_information_schema', array('timestamp' => $ts));
  }
  else
  {
    
  }
}

?>
