#! /usr/bin/php
<?php
foreach(array_slice($_SERVER['argv'], 1) as $file) {
  require_once($file);
}

Misago\Unit\TestCase::$batch_run = true;
Misago\Unit\TestCase::create_database(true);

Test\Unit\Autorunner::run('Unit Tests');

Misago\Unit\TestCase::drop_database(true);

?>
