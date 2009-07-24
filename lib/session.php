<?php

class Session
{
  static function start($session_id=null, $force_new_id=false)
  {
    # session was already started.
    if (isset($_SESSION)) {
      return;
    }
    ini_set('session.name', 'session_id');
    ini_set('session.use_cookies',   true);
    ini_set('session.use_trans_sid', false);
    
    if ($force_new_id) {
      session_regenerate_id();
    }
    elseif (isset($session_id)) {
      session_id($session_id);
    }
    
    session_start();
    
    # Tries to protect against session highjacking
    #   1. session must already exist ;
    #   2. a session_id cannot move from one browser to another!
    if (!isset($_SESSION['initialized'])
      or $_SESSION['user-agent'] != $_SERVER['HTTP_USER_AGENT'])
    {
      session_destroy();
      session_regenerate_id();
      session_start();
    }
    
    $_SESSION['initialized'] = true;
    $_SESSION['user-agent']  = $_SERVER['HTTP_USER_AGENT'];
    
    return session_id();
  }
  
  static function destroy()
  {
    if (!isset($_SESSION)) {
      return;
    }
    session_destroy();
    session_unset();
    setcookie(session_name(), '', time() - 42000, '/');
  }
}

?>
