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

# Extracts an excerpt from from text that matches the first instance of phrase.
# TODO: excerpt()
function excerpt($text, $phrase, $radius=100, $excerpt_string='...')
{
  
}

function highlight($text, $phrases, $highlighter='<mark>\1</mark>')
{
  if (is_array($phrases))
  {
    foreach($phrases as $i => $phrase) {
      $phrase[$i] = preg_quote($phrase);
    }
    $phrases = implode('|', $phrases);
  }
  return preg_replace('/('.$phrases.')/', $highlighter, $text);
}

# TODO: pluralize()
function pluralize($count, $singular, $plural=null)
{
  if ($count != 1) {
    return ($plural === null) ? String::pluralize($singular) : $plural;
  }
  return $singular;
}



?>
