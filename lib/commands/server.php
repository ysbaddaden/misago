<?php

# parameters
class ServerConfig
{
  static $environment = 'development';
  static $http_host   = 'localhost';
  static $http_port   = 3000;
  
  static $fcgi_bind;
  static $php_ini;
  
  static $server_conf;
  static $server_tmp_conf;
  
  static $http_server = 'lighttpd'; # either 'lighttpd' or 'nginx'
}

ServerConfig::$fcgi_bind = TMP.'/fastcgi-php.socket';
ServerConfig::$php_ini   = ROOT.'/config/php.ini';

switch(ServerConfig::$http_server)
{
  case 'lighttpd':
    ServerConfig::$server_conf     = ROOT.'/config/lighttpd.conf';
    ServerConfig::$server_tmp_conf = TMP.'/lighttpd.conf';
  break;
  
  case 'nginx':
    ServerConfig::$server_conf     = ROOT.'/config/nginx.conf';
    ServerConfig::$server_tmp_conf = TMP.'/nginx.conf';
  break;
}

for ($i=0; $i<$_SERVER['argc']; $i++)
{
  $arg = $_SERVER['argv'][$i];
  switch($arg)
  {
    case '-e': $i += 1; ServerConfig::$environment = $_SERVER['argv'][$i]; break;
    case '-h': $i += 1; ServerConfig::$http_host   = $_SERVER['argv'][$i]; break;
    case '-p': $i += 1; ServerConfig::$http_port   = $_SERVER['argv'][$i]; break;
  }
}

$vars = array(
  "#{ROOT}"        => ROOT,
  "#{PUBLIC_ROOT}" => ROOT.'/public',
  "#{MISAGO_ENV}"  => ServerConfig::$environment,
  "#{HTTP_HOST}"   => ServerConfig::$http_host,
  "#{HTTP_PORT}"   => ServerConfig::$http_port,
  "#{TMP}"         => TMP,
  "#{LOG}"         => ROOT.'/log',
  "#{FCGI_BIND}"   => ServerConfig::$fcgi_bind,
);

# server's config
$config_data = file_get_contents(ServerConfig::$server_conf);
$config_data = str_replace(array_keys($vars), array_values($vars), $config_data);
file_put_contents(ServerConfig::$server_tmp_conf, $config_data);


echo "=> Booting misago in '".ServerConfig::$environment."' environment\n";

# semaphores
file_put_contents(TMP."/msgqueue-".ServerConfig::$environment, '');
$msg_queue = msg_get_queue(ftok(TMP."/msgqueue-".ServerConfig::$environment, 'M'), 0666);

$descriptorspec = array(
   0 => array("pipe", "r"),
   1 => array("pipe", "w"),
   2 => array("pipe", "w") // stderr is a file to write to
);

# FastCGI service
echo "=> Starting FastCGI service at ".ServerConfig::$fcgi_bind."\n";
$fcgi_envs = array(
  'PATH'  => getenv('PATH'),
  'USER'  => getenv('USER'),
  'SHELL' => getenv('SHELL'),
  'MISAGO_ENV' => ServerConfig::$environment,
  'PHP_FCGI_CHILDREN'     => 5,
  'PHP_FCGI_MAX_REQUESTS' => 1000,
);
$fcgi_process = proc_open('exec php-cgi -b '.ServerConfig::$fcgi_bind, $descriptorspec, $pipes, null, $fcgi_envs);

# HTTP server
switch(ServerConfig::$http_server)
{
  case 'lighttpd': 
    echo "=> Starting LightTPD HTTP server at http://".ServerConfig::$http_host.":".ServerConfig::$http_port."/\n";
    $http_process = proc_open('exec lighttpd -Df '.ServerConfig::$server_tmp_conf, $descriptorspec, $pipes, null, null);
  break;
  
  case 'nginx': 
    echo "=> Starting NGINX HTTP server at http://".ServerConfig::$http_host.":".ServerConfig::$http_port."/\n";
    $http_process = proc_open('exec nginx -c '.ServerConfig::$server_tmp_conf, $descriptorspec, $pipes, null, null);
  break;
}

# signals (for clean shutdown)
declare(ticks = 1);

function server_sig_handler($signo)
{
  global $msg_queue, $fcgi_process, $http_process;
  echo "\nReceived signal. Exiting.\n";
  
  msg_remove_queue($msg_queue);
  unlink(TMP."/msgqueue-".ServerConfig::$environment);
  
  proc_terminate($fcgi_process);
  proc_terminate($http_process);
  exit;
}
pcntl_signal(SIGINT,  'server_sig_handler');
pcntl_signal(SIGTERM, 'server_sig_handler');


# logs messages (sent by FastCGI processes)
do
{
  if (msg_receive($msg_queue, 0, $msg_type, 16384, $message, false, MSG_IPC_NOWAIT, $errno)) {
    echo $message;
  }
  else {
    usleep(100000); # 100ms
  }
}
while(true);

?>
