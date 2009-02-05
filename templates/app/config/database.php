<?php

# DEPRECATED
class Database
{
  static $development = array(
    'adapter'  => 'mysql',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'example_app_development',
  );

  static $production = array(
    'adapter'  => 'mysql',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'example_app',
  );

  static $test = array(
    'adapter'  => 'mysql',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'example_app_test',
  );
}

?>
