<?php

# DEPRECATED: use Misago\ActionController\Base::url_for() instead.
function url_for($options=array())
{
  trigger_error("Use ActionController\Base::url_for() instead of url_for().", E_USER_DEPRECATED);
  return cfg_get('misago.current_controller')->url_for($options);
}

?>
