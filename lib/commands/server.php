<?php

# defaults
$root = ROOT;
$http_host = 'localhost';
$http_port = 3000;
$environment = 'development';

# parameters
for ($i=0; $i<$_SERVER['argc']; $i++)
{
  $arg = $_SERVER['argv'][$i];
  
  switch($arg)
  {
    case '-e': 
      $i += 1;
      $environment = $_SERVER['argv'][$i];
    break;
    
    case '-h':
      $i += 1;
      $http_host = $_SERVER['argv'][$i];
    break;
    
    case '-p':
      $i += 1;
      $http_port = $_SERVER['argv'][$i];
    break;
  }
}

# lighttpd's config
$config_file = ROOT.'/config/lighttpd.conf';
$config_data = file_get_contents($config_file);

$vars = array(
  "#{ROOT}"        => ROOT,
  "#{PUBLIC_ROOT}" => ROOT.'/public',
  "#{MISAGO_ENV}"  => $environment,
  "#{HTTP_HOST}"   => $http_host,
  "#{HTTP_PORT}"   => $http_port,
  "#{TMP}"         => TMP,
  "#{LOG}"         => ROOT.'/log',
);
$config_data = str_replace(array_keys($vars), array_values($vars), $config_data);
$config_file = TMP.'/lighttpd.conf';
file_put_contents($config_file, $config_data);

# signals
declare(ticks = 1);

# semaphores
file_put_contents(TMP."/msgqueue-$environment", '');
$msg_queue = msg_get_queue(ftok(TMP."/msgqueue-$environment", 'M'), 0666);

# let's fork!
$pid = pcntl_fork();
if ($pid == -1) {
  die("Could not fork.\n");
}
elseif($pid)
{
  # cleanup when signal is received
  function server_sig_handler($signo)
  {
    global $msg_queue, $environment;
    
    msg_remove_queue($msg_queue);
    unlink(TMP."/msgqueue-$environment");
    
    echo "\nReceived signal. Exiting.\n";
    exit;
  }
  pcntl_signal(SIGINT,  'server_sig_handler');
  pcntl_signal(SIGTERM, 'server_sig_handler');
  
  # logger
  do
  {
    if (msg_receive($msg_queue, 0, $msg_type, 16384, $message, false, MSG_IPC_NOWAIT, $errno)) {
      echo $message;
    }
    else
    {
      # 100ms
      usleep(100000);
    }
  }
  while(true);
  
  # prevents against zombies
  pcntl_wait($status);
}
else
{
  # starts server
  echo "=> Starting lighttpd at http://$http_host:$http_port/\n";
  echo "=> MISAGO_ENV={$environment} lighttpd -Df {$config_file}\n\n";
  
  passthru("MISAGO_ENV={$environment} lighttpd -Df {$config_file}");
}

?>
