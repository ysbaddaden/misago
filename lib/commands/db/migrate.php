<?php
$action = null;

foreach($_SERVER['argv'] as $v)
{
  switch($v)
  {
    case 'up':   $action = 'up';   break;
    case 'down': $action = 'down'; break;
    case 'redo': $action = 'redo'; break;
    case '--help':
      
      echo "\n";
      echo "$ script/db/migrate\n";
      echo "   => will run all required migrations up to the latest one.\n";
      echo "\n";
      echo "$ script/db/migrate VERSION=XXX\n";
      echo "   => will run all required migrations (up or down) until the target\n";
      echo "      is reached. On up the target migration is runned. On down the\n";
      echo "      target migration isn't.\n";
      echo "\n";
      echo "$ script/db/migrate redo\n";
      echo "   => will migrate down the last migration, before migrating up again.\n";
      echo "\n";
      echo "$ script/db/migrate redo STEP=2\n";
      echo "   => will migrate down the last 2 migrations, before migrating up again.\n";
      echo "\n";
    
    exit;
    default:
      if (preg_match('/^([A-Z_]+)=(.+)$/', $v, $match)) {
        define($match[1], $match[2]);
      }
  }
}
$from_version = ActiveRecord_Migration::get_version();
$migrations   = ActiveRecord_Migration::migrations();

switch($action)
{
  case 'up':
    echo "script/db/migrate up VERSION=XXX is unsupported for now.\n";
  break;
  
  case 'down':
    echo "script/db/migrate down VERSION=XXX is unsupported for now.\n";
  break;
  
  case 'redo':
    if (!defined('STEP')) {
      define('STEP', 1);
    }
    if (STEP > 0)
    {
      # migrates down x steps
      $migration = end($migrations);
      for($i=0; $i<STEP; $i++)
      {
        ActiveRecord_Migration::run($migration, 'down');
        $migration = prev($migrations);
      }
      
      # migrates up x steps
      for($i=0; $i<STEP; $i++) {
        ActiveRecord_Migration::run(next($migrations), 'up');
      }
    }
    else {
      echo "Error.\n";
    }
  break;
  
  default:
    if (!defined('VERSION'))
    {
      $last = end($migrations);
      define('VERSION', $last['version']);
    }
    
    if ($from_version < VERSION)
    {
      # migrates up
      foreach($migrations as $migration)
      {
        if ($migration['version'] > $from_version
          and $migration['version'] <= VERSION)
        {
          ActiveRecord_Migration::run($migration, 'up');
        }
      }
    }
    elseif ($from_version > VERSION)
    {
      # migrates down
      $migration = end($migrations);
      while($migration !== false)
      {
        if ($migration['version'] <= $from_version
          and $migration['version'] > VERSION)
        {
          ActiveRecord_Migration::run($migration, 'down');
        }
        $migration = prev($migrations);
      }
    }
    else {
      echo "Database is up to date.\n";
    }
  break;
}

?>
