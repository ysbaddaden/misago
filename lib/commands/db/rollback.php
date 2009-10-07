<?php
#   script/db/rollback
#     => rollbacks the last migration
#   
#   script/db/rollback STEP=3
#     => rollbacks the last 3 migrations

foreach($_SERVER['argv'] as $v)
{
  if (preg_match('/^([A-Z_]+)=(.+)$/', $v, $match)) {
    define($match[1], $match[2]);
  }
}
if (!defined('STEP')) {
  define('STEP', 1);
}

$from_version = ActiveRecord_Migration::get_version();
$migrations   = ActiveRecord_Migration::migrations();

if (STEP > 0)
{
  $migration = reset($migrations);
  while($migration !== false)
  {
    if ($migration['version'] == $from_version)
    {
      for($i=0; $i<STEP; $i++)
      {
        ActiveRecord_Migration::run($migration, 'down');
        $migration = prev($migrations);
        if ($migration === false) break;
      }
      break;
    }
    $migration = next($migrations);
  }
}

?>
