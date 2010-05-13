<?php

# Renders HTML tags to include assets like images, stylesheets and
# javascript files in your pages.
# 
# =Timestamps
# 
# The last modification date is always added to all local and existing
# assets. This permits for browser-side HTTP caching. The actual HTTP
# caching must be handled by your web server (ie. the 'compress' module
# of LightTPD).
# 
namespace Misago\ActionView\Helpers\AssetTag;

# :nodoc:
function linearize_path($base_path, $path)
{
  if (is_array($path)) {
    return cfg_get('misago.current_controller')->url_for($path);
  }
  
  $path = trim($path);
  if (strpos($path, 'http://') !== 0)
  {
    if (strpos($path, '/') !== 0) {
      $path = "$base_path/$path";
    }
    if (file_exists(ROOT.'/public'.$path)) {
      $path .= '?'.filemtime(ROOT.'/public'.$path);
    }
  }
  return $path;
}

?>
