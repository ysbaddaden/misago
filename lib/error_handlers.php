<?php

# Renders a trace as string (html or text)
function debug_render_backtrace($backtrace, $var_export=true)
{
  $html = ini_get('html_errors');
  $idx  = 0;
  $str  = '';
  
  foreach($backtrace as $trace)
  {
    $callee  = isset($trace['class']) ? $trace['class'].$trace['type'] : '';
    $callee .= $trace['function'];
    
    if (isset($trace['args']))
    {
      if ($var_export)
      {
        foreach($trace['args'] as $k => $v) {
          $trace['args'][$k] = var_export($v, true);
        }
      }
      $args = implode(", ", $trace['args']);
    }
    else {
      $args = '';
    }
    
    if ($html)
    {
      $file = str_replace(ROOT, '', $trace['file']);
      $str .= "<tr>".
          "<td title=\"{$file} (line {$trace['line']})\">$callee($args)</td>".
        "</tr>";
    }
    else {
      $str .= "#$idx {$callee}($args)\n";
    }
    $idx++;
  }
  return $html ? "<table class=\"trace\"><caption>Backtrace:</caption>$str</table>" : $str;
}

# TODO: Render an exception page in production environment.
function error_handler($errno, $errstr, $errfile=null, $errline=null, array $errcontext=null)
{
  $backtrace = debug_backtrace();
  unset($backtrace[0]);
  $trace = debug_render_backtrace($backtrace);
  
#  if ($_SERVER['MISAGO_ENV'] == 'production') {
    error_log("$errstr [$errno] in $errfile (line $errline)\n$trace\n", 0);
#  }
#  else
#  {
#    echo "\n".Terminal::colorize($errstr, 'RED')."\n  in $errfile(line $errline)\n";
##    echo Terminal::colorize($trace, 'LIGHT_GRAY')."\n";
#  }
}

function exception_handler($e)
{
  $message = $e->getMessage();
  $trace   = debug_render_backtrace($e->getTrace(), false);
  
  if ($_SERVER['MISAGO_ENV'] == 'production') {
    error_log("Exception: $message\n$trace\n", 0);
  }
  else
  {
    echo "\n".Terminal::colorize("Exception: $message", 'RED')."\n  in ".$e->getFile()." (line ".$e->getLine().")\n";
    echo Terminal::colorize($trace, 'LIGHT_GRAY')."\n";
    die();
  }
}

if ($_SERVER['MISAGO_ENV'] == 'production') {
  set_error_handler('error_handler');
}
set_exception_handler('exception_handler');

?>
