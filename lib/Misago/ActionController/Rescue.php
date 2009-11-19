<?php
namespace Misago\ActionController;
use Misago\Terminal;

# You may overwrite any of these methods to handle errors and exceptions
# raised within your application.
abstract class Rescue extends \Misago\Object
{
  protected $rescue_errors   = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
  protected $rescue_warnings = array(E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_RECOVERABLE_ERROR);
  protected $rescue_notices  = array(E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, E_STRICT);
  public    $logger;
  
  function __construct()
  {
    $this->logger = \Misago\Logger::singleton();
    
    if ($_SERVER['MISAGO_ENV'] == 'production') {
      set_error_handler(array($this, 'rescue_php_error'));
    }
  }
  
  # Checks wether the request originates from localhost or a remote computer.
  function is_local_request()
  {
    return (PHP_SAPI == 'cli' or $_SERVER['REMOTE_ADDR'] == '127.0.0.1' or $this->request->remote_ip() == '127.0.0.1');
  }
  
  # Catches PHP errors and logs them.
  function rescue_php_error($errno, $errstr, $errfile=null, $errline=null, array $errcontext=null)
  {
    $php_errors = array(
      E_ERROR => 'E_ERROR', E_PARSE => 'E_PARSE', E_CORE_ERROR => 'E_CORE_ERROR',
      E_COMPILE_ERROR => 'E_COMPILE_ERROR', E_USER_ERROR => 'E_USER_ERROR',
      E_WARNING => 'E_WARNING', E_USER_WARNING => 'E_USER_WARNING',
      E_CORE_WARNING => 'E_CORE_WARNING', E_COMPILE_WARNING => 'E_COMPILE_WARNING',
      E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR', E_NOTICE => 'E_NOTICE',
      E_USER_NOTICE => 'E_USER_NOTICE', E_DEPRECATED => 'E_DEPRECATED',
      E_USER_DEPRECATED => 'E_USER_DEPRECATED', E_STRICT => 'E_STRICT'
    );
    $message = "{$php_errors[$errno]}: $errstr, line $errline in file $errfile";
    
    if (in_array($errno, $this->rescue_errors))
    {
      $this->logger->error($message);
      die();
    }
    elseif (in_array($errno, $this->rescue_warnings)) {
      $this->logger->warning($message);
    } 
    elseif (in_array($errno, $this->rescue_notices)) {
      $this->logger->notice($message);
    }
    else {
      $this->logger->unknown();
    }
  }
  
  # Logs an exception. By default logs as ERROR.
  function log_error($exception)
  {
    $trace = str_replace("\n", "\n  ", $exception->getTraceAsString());
    $this->logger->error("\n".get_class($exception).' ('.$exception->getMessage().")\n  ".$trace."\n");
  }
  
  # Catches exceptions raised within an action.
  function rescue_action($exception)
  {
    $this->log_error($exception);
    
    if ($this->is_local_request()) {
      $this->rescue_action_locally($exception);
    }
    else {
      $this->rescue_action_in_public($exception);
    }
  }
  
  # Catches exceptions raised on production, to remote computers.
  # Defaults to call <tt>render_optional_error_file()</tt>.
  function rescue_action_in_public($exception)
  {
    $status_code = ($exception instanceof \Misago\Exception) ? $exception->getCode() : 500;
    $this->render_optional_error_file($status_code);
  }
  
  # Tries to render a static error page. At first it tries to load a localized file
  # (for instance +500.fr.html+), then tries to load a generic file (+500.html+),
  # and falls back to display the raw error.
  function render_optional_error_file($status_code)
  {
    $status = $this->response->status($status_code);
    
    if (file_exists(ROOT.'/public/'.$status_code.'.'.I18n::$locale.'.html')) {
      $this->response->body = file_get_contents(ROOT.'/public/'.$status_code.'.'.I18n::$locale.'.html');
    }
    elseif (file_exists(ROOT.'/public/'.$status_code.'.html')) {
      $this->response->body = file_get_contents(ROOT.'/public/'.$status_code.'.html');
    }
    else {
      echo "<!DOCTYPE html><html><head><title>$status</title></head><body><h1>$status</h1></body></html>";
    }
    
    $this->response->send();
    die();
  }
  
  # Catches exceptions raised locally (for instance in development or on the server).
  # Displays detailed informations about the raised exception.
  function rescue_action_locally($exception)
  {
    $message = $exception->getMessage();
    
    if (PHP_SAPI != 'cli')
    {
      $this->response->status($exception->getCode());
      
      $body  = '<!DOCTYPE html>'."\n";
      $body .= '<html>'."\n";
      $body .= '<head>'."\n";
      $body .= '<style type="text/css">'."\n";
      $body .= '  body { font: normal 12px/1.5 sans-serif; color: #333; background: #FFF; }'."\n";
      $body .= '  pre { font: normal 12px/1.5 monospace; background: #F4F4F4; padding: 1em; }'."\n";
      $body .= '</style>'."\n";
      $body .= '</head>'."\n";
      $body .= '<body>'."\n";
      
      $body .= '<h1>'.$message.'</h1>'."\n";
      $body .= '<p>MISAGO_ROOT: '.ROOT.'</p>'."\n";
      $body .= '<pre>'.$exception->getTraceAsString().'</pre>'."\n";
      
      $body .= '<h2>Request</h2>'."\n";
      $body .= '<h3>Parameters</h3>'."\n";
      $body .= '<pre>'.print_r($this->params, true).'</pre>'."\n";
      $body .= '<h3>Session data</h3>'."\n";
      $body .= '<pre>'.print_r($_SESSION, true).'</pre>'."\n";
      
      $body .= '<h2>Response</h2>'."\n";
      $body .= '<h3>headers</h3>'."\n";
      $body .= '<pre>'.print_r($this->response->headers, true).'</pre>'."\n"; 
      
      $body .= '</body>'."\n";
      $body .= '</html>'."\n";
      
      $this->response->body = $body;
      $this->response->send();
    }
    else
    {
      echo "\n".Terminal::colorize($message, 'RED')."\n";
      echo Terminal::colorize($exception->getTraceAsString(), 'LIGHT_GRAY')."\n";
    }
    die();
  }
}

?>
