<?php
/**
 * Generic exception handler for the framework.
 */
class MisagoException extends Exception
{
  # TODO: Log error data & display a standard error page in production environment. 
  function render()
  {
    header('Status: '.$this->getCode(), true);
    
    $message = $this->getMessage();
    $code    = $this->getCode();
    $trace   = debug_render_backtrace($this->getTrace());
    
    echo <<<END_CSS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$message}</h1>
  <style type="text/css">
  body  { font: normal 12px/1.5 sans-serif; }
  .exception h1 { color: red; margin-bottom: 0; }
  .trace { font: 12px/1.5 monospace; border-bottom: 4px solid #EEE; border-collapse: collapse; }
  .trace caption { font-weight: bold; text-align: left; background: #EEE; }
  .trace td { white-space: nowrap; border-bottom: 1px dotted #EEE; }
  .trace td:hover { background: #EEE; }
  .trace .where { font: 10px/1.0 sans-serif; color: #888; overflow: hidden; }
  </style>
</head>
<body>
  <div class="exception">
    <h1>Error {$code}: {$message}</h1>
    {$trace}
  </div>
</body>
</html>
END_CSS;
  }
}

?>
