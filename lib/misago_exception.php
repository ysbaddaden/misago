<?php
/**
 * Generic exception handler for the framework.
 */
class MisagoException extends Exception
{
  # TODO Log error data & display a standard error page in production environment. 
  function render()
  {
    HTTP::status($this->getCode());
    
    $message = $this->getMessage();
    $code    = $this->getCode();
    $trace   = $this->getTraceAsHtml();
    
    echo <<<END_CSS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>$message</h1>
  <style type="text/css">
  body { font: normal 12px/1.5 sans-serif; }
  dl { margin-left: 1.5em; }
  dt { font-weight: bold; }
  dd { margin: 0 0 .5em 1.5em; font-family: monospace; }
  </style>
</head>
<body>
  <h1>Error $code: $message</h1>
  <h2>Trace callback:</h2>
  $trace
</body>
</html>
END_CSS;
  }
  
  function getTraceAsHtml()
  {
    $str = '';
    
    foreach($this->getTrace() as $i => $trace)
    {
      $file    = str_replace(ROOT, '', $trace['file']);
      
      $callee  = isset($trace['class']) ? $trace['class'].$trace['type'] : '';
      $callee .= $trace['function'];
      
      foreach($trace['args'] as $k => $v) {
        $trace['args'][$k] = var_export($v, true);
      }
      $args = implode(", ", $trace['args']);
      
      $str .= <<<END_TRACE
<dt>{$file} at line {$trace['line']}</dt>
<dd>$callee($args)</dd>
END_TRACE;
    }
    
    return "<dl>$str</dl>";
  }
}

?>
