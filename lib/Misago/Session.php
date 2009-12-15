<?php
namespace Misago;

class Session
{
  # Starts a session (if none already exists).
  # Returns the session's id.
  static function start($session_id=null, $force_new_id=false)
  {
    # session has already been started.
    if (isset($_SESSION)) {
      return;
    }
    
    # config
    ini_set('session.name', 'session_id');
    ini_set('session.use_cookies', PHP_SAPI == 'cli' ? true : false);
    ini_set('session.use_trans_sid', false);
    
    if ($force_new_id) {
      session_regenerate_id();
    }
    elseif (!empty($session_id)) {
      session_id($session_id);
    }
    session_start();
    
    return Session::init();
  }
  
  # Destroys current session and starts up another one.
  static function restart($session_id=null)
  {
    Session::destroy();
    ($session_id === null) ? session_regenerate_id() : session_id($session_id);
    session_start();
    return Session::init();
  }
  
  # Destroys current session.
  static function destroy()
  {
    if (!isset($_SESSION)) {
      return;
    }
    session_destroy();
    session_unset();
    setcookie(session_name(), '', time() - 42000, '/');
  }
  
  # Tries to protect against session highjacking
  # 
  # 1. session must already exist;
  # 2. a session_id cannot move from one browser to another.
  # 
  # Returns the session's id.
  private static function init()
  {
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    
    if (!isset($_SESSION['initialized'])
      or $_SESSION['user-agent'] != $user_agent)
    {
      session_destroy();
      session_regenerate_id();
      session_start();
    }
    
    $_SESSION['initialized'] = true;
    $_SESSION['user-agent']  = $user_agent;
    
    return session_id();
  }
}

?>
