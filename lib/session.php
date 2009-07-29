<?php

class Session
{
  static function start($session_id=null, $force_new_id=false)
  {
    # session was already started.
    if (isset($_SESSION)) {
      return;
    }
    
    # config
    ini_set('session.name', 'session_id');
    ini_set('session.use_cookies',   true);
    ini_set('session.use_trans_sid', false);
    
    if ($force_new_id) {
      session_regenerate_id();
    }
    else
    {
      if ($session_id === null and !empty($_REQUEST['session_id'])) {
        $session_id = $_REQUEST['session_id'];
      }
      if (!empty($session_id)) {
        session_id($session_id);
      }
    }
    session_start();
    
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    
    # Tries to protect against session highjacking
    #   1. session must already exist;
    #   2. a session_id cannot move from one browser to another.
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
