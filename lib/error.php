<?php

function error_handler($errno, $errstr, $errfile=null, $errline=null, array $errcontext=null)
{
  ob_start();
  debug_print_backtrace();
  $trace = ob_get_clean();
  error_log("$errstr [$errno] in $err_file($err_line)\n$trace\n", 0);
}

function exception_handler($e)
{
  $message = $this->getMessage();
  $code    = $this->getCode();
  $trace   = $this->getTraceAsString();
  error_log("$message [$code]\n$trace\n", 0);
}

set_error_handler('error_handler');
set_exception_handler('exception_handler');

?>
