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

function pluralize($count, $singular, $plural=null)
{
  if ($count != 1) {
    return ($plural === null) ? String::pluralize($singular) : $plural;
  }
  return $singular;
}

# Simple formatting of text. Two linefeeds make for a paragraph,
# and a single linefeed makes for line break.
function simple_format($text)
{
  $text = str_replace("\r", "", trim($text));
  $text = preg_replace("/\n\n+/", "</p><p>", $text);
  $text = preg_replace("/\n/", "<br/>", $text);
  return "<p>$text</p>";
}

# Truncates a string if it's longer than length, and appends a truncate string to it.
function truncate($text, $length=30, $truncate_string='...')
{
  if (strlen($text) > $length) {
    return substr($text, 0, $length - strlen($truncate_string)).$truncate_string;
  }
  return $text;
}

# TODO: markdown()
function markdown($text)
{
  
}

# TODO: textilize()
function textilize($text)
{
  
}

?>
