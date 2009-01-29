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

$config_file = 'config/lighttpd.conf';
$config_data = file_get_contents($config_file);

$vars = array(
  "#{ROOT}"        => ROOT,
  "#{PUBLIC_ROOT}" => ROOT.'/public',
  "#{ENVIRONMENT}" => $environment,
  "#{HTTP_HOST}"   => $http_host,
  "#{HTTP_PORT}"   => $http_port,
  "#{TMP}"         => TMP,
  "#{LOG}"         => ROOT.'/log',
);
$config_data = str_replace(array_keys($vars), array_values($vars), $config_data);

$config_file .= '.tmp';
file_put_contents($config_file, $config_data);

# starts server
echo "Starting lighttpd at http://$http_host:$http_port/\n";
echo "environment={$environment} lighttpd -Df {$config_file}\n";
`environment={$environment} lighttpd -Df {$config_file}`;

?>
