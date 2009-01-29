<?php

class DatabaseConfig
{
  static $development = array(
    'driver'   => 'mysql',
    'username' => 'root',
    'password' => '',
    'database' => 'example_app_development',
  );

  static $production = array(
    'driver'   => 'mysql',
    'username' => 'root',
    'password' => '',
    'database' => 'example_app',
  );

  static $test = array(
    'driver'   => 'mysql',
    'username' => 'root',
    'password' => '',
    'database' => 'example_app_test',
  );
}

?>
