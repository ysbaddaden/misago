<?php
# Syntaxes:
# 
#   $ script/db/migrate
#     => migrates from current_version to latest_version
#   
#   $ script/db/migrate VERSION=XXX
#     => if version > current_version: migrates up
#     => if version < current_version: migrates down
#   
#   $ script/db/migrate up VERSION=XXX
#     => runs a single migration up (will do nothing if already done)
#   
#   $ script/db/migrate up VERSION=XXX
#     => runs a single migration down (will do nothing if not done)
#   
#   $ script/db/migration redo
#     => rollbacks the last migration and then migrates it up again
#   
#   $ script/db/migration redo STEP=3
#     => rollbacks the last 3 migrations and then migrates them up again

$action = null;

foreach($_SERVER['argv'] as $v)
{
  switch($v)
  {
    case 'up':   $action = 'up';   break;
    case 'down': $action = 'down'; break;
    case 'redo': $action = 'redo'; break;
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
