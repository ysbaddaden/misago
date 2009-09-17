<?php

class ActionView_Helpers_TextHelper_NS
{
  # @private
  static function preg_replace_urls($match)
  {
    return link_to($match[0]);
  }
  
  # @private
  static function preg_replace_email_addresses($match)
  {
    return mail_to($match[0]);
  }
}

# Transforms all links and email addresses to clickable links.
# 
# - link limits what should be linked: all, email_addresses, urls.
# - callback permits to use a function for preg_replace_callback that will replace link's text.
# 
# @namespace ActionView_Helpers_TextHelper
function auto_link($text, $link='all'/*, $href_options=null, $callback=null*/)
{
  if ($link == 'all' or $link == 'urls')
  {
    $text = preg_replace_callback('/(?:http|https|ftp|sftp|ssh):\/\/[^ ]+/',
      array('ActionView_Helpers_TextHelper_NS', 'preg_replace_urls'), $text);
  }
  if ($link == 'all' or $link == 'email_addresses')
  {
    $text = preg_replace_callback('/[^@ ]+\@[^ ]+/',
      array('ActionView_Helpers_TextHelper_NS', 'preg_replace_email_addresses'), $text);
  }
  return $text;
}

?>
